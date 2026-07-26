<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FormSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->isCustomer()) {
            return response()->json(['message' => 'Only customers can create bookings.'], 403);
        }

        $payload = $request->validate([
            'payload' => ['required', 'array'],
            'payload.name' => ['required', 'string', 'max:120'],
            'payload.mobile' => ['required', 'string', 'max:40'],
            'payload.category' => ['required', 'string', 'max:120'],
            'payload.service' => ['required', 'string', 'max:160'],
            'payload.city' => ['required', 'string', 'max:80'],
            'payload.address' => ['required', 'string', 'max:255'],
        ])['payload'];

        $booking = FormSubmission::create([
            'user_id' => $user->id,
            'type' => 'booking',
            'reference' => 'B-'.now()->format('ymd').'-'.Str::upper(Str::random(6)),
            'payload' => $payload,
            'status' => FormSubmission::STATUS_RECEIVED,
        ]);

        $booking->load(['user', 'provider']);

        return response()->json([
            'message' => 'Booking created.',
            'reference' => $booking->reference,
            'booking' => $booking->toBookingArray(),
        ], 201);
    }

    public function mine(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->isCustomer()) {
            return response()->json(['message' => 'Only customers can view their bookings.'], 403);
        }

        $items = FormSubmission::query()
            ->with(['user', 'provider'])
            ->where('type', 'booking')
            ->where('user_id', $user->id)
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn (FormSubmission $item) => $item->toBookingArray())
            ->values();

        return response()->json(['data' => $items]);
    }

    public function show(Request $request, FormSubmission $booking): JsonResponse
    {
        if (! $booking->isBooking()) {
            return response()->json(['message' => 'Not a booking.'], 404);
        }

        $user = $request->user();
        $allowed = ($user->isCustomer() && $booking->user_id === $user->id)
            || ($user->isProvider() && (
                $booking->provider_id === $user->id
                || $booking->status === FormSubmission::STATUS_RECEIVED
            ));

        if (! $allowed) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $booking->load(['user', 'provider']);

        return response()->json(['booking' => $booking->toBookingArray()]);
    }

    public function openRequests(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->isProvider()) {
            return response()->json(['message' => 'Only providers can view booking requests.'], 403);
        }

        $search = trim((string) $request->query('q', ''));
        $category = trim((string) $request->query('category', ''));

        $query = FormSubmission::query()
            ->with(['user', 'provider'])
            ->where('type', 'booking')
            ->where('status', FormSubmission::STATUS_RECEIVED)
            ->whereNull('provider_id')
            ->latest();

        $items = $query->limit(100)->get();

        if ($category !== '' && strcasecmp($category, 'All') !== 0) {
            $items = $items->filter(function (FormSubmission $item) use ($category) {
                return strcasecmp((string) ($item->payload['category'] ?? ''), $category) === 0;
            })->values();
        }

        if ($search !== '') {
            $needle = Str::lower($search);
            $items = $items->filter(function (FormSubmission $item) use ($needle) {
                $payload = $item->payload ?? [];
                $hay = Str::lower(implode(' ', [
                    $item->reference,
                    $payload['category'] ?? '',
                    $payload['service'] ?? '',
                    $payload['city'] ?? '',
                    $payload['name'] ?? '',
                    $payload['urgency'] ?? '',
                ]));

                return str_contains($hay, $needle);
            })->values();
        }

        // Prefer provider category matches first when profile has one
        $providerCategory = $user->profile['category'] ?? null;
        if ($providerCategory) {
            $items = $items->sortByDesc(function (FormSubmission $item) use ($providerCategory) {
                return strcasecmp((string) ($item->payload['category'] ?? ''), $providerCategory) === 0 ? 1 : 0;
            })->values();
        }

        return response()->json([
            'data' => $items->map(fn (FormSubmission $item) => $item->toBookingArray())->values(),
        ]);
    }

    public function myJobs(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->isProvider()) {
            return response()->json(['message' => 'Only providers can view jobs.'], 403);
        }

        $status = $request->query('status');

        $query = FormSubmission::query()
            ->with(['user', 'provider'])
            ->where('type', 'booking')
            ->where('provider_id', $user->id)
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $items = $query->limit(100)->get()
            ->map(fn (FormSubmission $item) => $item->toBookingArray())
            ->values();

        return response()->json(['data' => $items]);
    }

    public function accept(Request $request, FormSubmission $booking): JsonResponse
    {
        $user = $request->user();

        if (! $user->isProvider()) {
            return response()->json(['message' => 'Only providers can accept bookings.'], 403);
        }

        if (! $booking->isBooking()) {
            return response()->json(['message' => 'Not a booking.'], 404);
        }

        if ($booking->status !== FormSubmission::STATUS_RECEIVED || $booking->provider_id) {
            return response()->json(['message' => 'This booking is no longer available.'], 422);
        }

        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $booking->update([
            'provider_id' => $user->id,
            'status' => FormSubmission::STATUS_ASSIGNED,
            'provider_note' => $data['note'] ?? null,
            'accepted_at' => now(),
        ]);

        $booking->load(['user', 'provider']);

        return response()->json([
            'message' => 'Booking accepted.',
            'booking' => $booking->toBookingArray(),
        ]);
    }

    public function start(Request $request, FormSubmission $booking): JsonResponse
    {
        return $this->providerTransition(
            $request,
            $booking,
            FormSubmission::STATUS_ASSIGNED,
            FormSubmission::STATUS_IN_PROGRESS,
            ['started_at' => now()],
            'Job started.',
        );
    }

    public function complete(Request $request, FormSubmission $booking): JsonResponse
    {
        return $this->providerTransition(
            $request,
            $booking,
            FormSubmission::STATUS_IN_PROGRESS,
            FormSubmission::STATUS_COMPLETED,
            ['completed_at' => now()],
            'Job completed.',
        );
    }

    public function cancel(Request $request, FormSubmission $booking): JsonResponse
    {
        $user = $request->user();

        if (! $booking->isBooking()) {
            return response()->json(['message' => 'Not a booking.'], 404);
        }

        $isCustomerOwner = $user->isCustomer() && $booking->user_id === $user->id;
        $isAssignedProvider = $user->isProvider() && $booking->provider_id === $user->id;

        if (! $isCustomerOwner && ! $isAssignedProvider) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (! in_array($booking->status, [
            FormSubmission::STATUS_RECEIVED,
            FormSubmission::STATUS_ASSIGNED,
        ], true)) {
            return response()->json(['message' => 'This booking can no longer be cancelled.'], 422);
        }

        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $booking->update([
            'status' => FormSubmission::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'provider_note' => $data['note'] ?? $booking->provider_note,
        ]);

        $booking->load(['user', 'provider']);

        return response()->json([
            'message' => 'Booking cancelled.',
            'booking' => $booking->toBookingArray(),
        ]);
    }

    private function providerTransition(
        Request $request,
        FormSubmission $booking,
        string $from,
        string $to,
        array $extra,
        string $message,
    ): JsonResponse {
        $user = $request->user();

        if (! $user->isProvider()) {
            return response()->json(['message' => 'Only providers can update jobs.'], 403);
        }

        if (! $booking->isBooking()) {
            return response()->json(['message' => 'Not a booking.'], 404);
        }

        if ($booking->provider_id !== $user->id) {
            return response()->json(['message' => 'This job is not assigned to you.'], 403);
        }

        if ($booking->status !== $from) {
            return response()->json([
                'message' => "Booking must be '{$from}' before moving to '{$to}'.",
            ], 422);
        }

        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $booking->update(array_merge([
            'status' => $to,
            'provider_note' => $data['note'] ?? $booking->provider_note,
        ], $extra));

        $booking->load(['user', 'provider']);

        return response()->json([
            'message' => $message,
            'booking' => $booking->toBookingArray(),
        ]);
    }
}
