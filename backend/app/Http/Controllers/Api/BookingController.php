<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FormSubmission;
use App\Services\NotificationService;
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
            'payload.budget' => ['nullable', 'numeric', 'min:0'],
        ])['payload'];

        $budget = isset($payload['budget']) && $payload['budget'] !== ''
            ? (float) $payload['budget']
            : null;

        $booking = FormSubmission::create([
            'user_id' => $user->id,
            'type' => 'booking',
            'reference' => 'B-'.now()->format('ymd').'-'.Str::upper(Str::random(6)),
            'payload' => $payload,
            'customer_budget' => $budget,
            'current_offer' => $budget,
            'current_offer_by' => $budget !== null ? 'customer' : null,
            'offers' => $budget !== null ? [[
                'by' => 'customer',
                'amount' => $budget,
                'note' => 'Initial customer budget',
                'at' => now()->toIso8601String(),
            ]] : [],
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
            'amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $updates = [
            'provider_id' => $user->id,
            'status' => FormSubmission::STATUS_ASSIGNED,
            'provider_note' => $data['note'] ?? null,
            'accepted_at' => now(),
        ];

        if (isset($data['amount'])) {
            $amount = (float) $data['amount'];
            $offers = $booking->offers ?? [];
            $offers[] = [
                'by' => 'provider',
                'amount' => $amount,
                'note' => $data['note'] ?? 'Initial provider quotation',
                'at' => now()->toIso8601String(),
            ];
            $updates['offers'] = $offers;
            $updates['current_offer'] = $amount;
            $updates['current_offer_by'] = 'provider';
            $updates['status'] = FormSubmission::STATUS_QUOTED;
            $updates['quoted_at'] = now();
        }

        $booking->update($updates);
        $booking->load(['user', 'provider']);

        if ($booking->user) {
            $hasQuote = isset($data['amount']);
            NotificationService::push(
                $booking->user,
                'booking',
                $hasQuote ? 'Provider sent a quotation' : 'Provider accepted your booking',
                $hasQuote
                    ? $user->name.' quoted PKR '.number_format((float) $data['amount']).' on '.$booking->reference
                    : $user->name.' accepted '.$booking->reference.'. You can negotiate the budget.',
                '/dashboard/customer/bookings/'.$booking->id,
                $booking->id,
            );
        }

        return response()->json([
            'message' => isset($data['amount'])
                ? 'Booking accepted with quotation.'
                : 'Booking accepted. You can now negotiate the budget.',
            'booking' => $booking->toBookingArray(),
        ]);
    }

    public function propose(Request $request, FormSubmission $booking): JsonResponse
    {
        if (! $booking->isBooking()) {
            return response()->json(['message' => 'Not a booking.'], 404);
        }

        $user = $request->user();
        $isCustomer = $user->isCustomer() && $booking->user_id === $user->id;
        $isProvider = $user->isProvider() && $booking->provider_id === $user->id;

        if (! $isCustomer && ! $isProvider) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (! $booking->canNegotiate()) {
            return response()->json(['message' => 'Budget can only be negotiated after a provider is assigned and before the deal is finalized.'], 422);
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $by = $isCustomer ? 'customer' : 'provider';
        $amount = (float) $data['amount'];
        $offers = $booking->offers ?? [];
        $offers[] = [
            'by' => $by,
            'amount' => $amount,
            'note' => $data['note'] ?? ($by === 'provider' ? 'Provider quotation' : 'Customer budget update'),
            'at' => now()->toIso8601String(),
        ];

        $updates = [
            'offers' => $offers,
            'current_offer' => $amount,
            'current_offer_by' => $by,
            'provider_note' => $data['note'] ?? $booking->provider_note,
        ];

        if ($by === 'customer') {
            $updates['customer_budget'] = $amount;
            $payload = $booking->payload ?? [];
            $payload['budget'] = $amount;
            $updates['payload'] = $payload;
            // Customer counter keeps negotiation open
            $updates['status'] = FormSubmission::STATUS_ASSIGNED;
        } else {
            $updates['status'] = FormSubmission::STATUS_QUOTED;
            $updates['quoted_at'] = now();
        }

        $booking->update($updates);
        $booking->load(['user', 'provider']);

        if ($by === 'provider' && $booking->user) {
            NotificationService::push(
                $booking->user,
                'quote',
                'New quotation on '.$booking->reference,
                'Provider quoted PKR '.number_format($amount).'. Review and accept to unlock chat.',
                '/dashboard/customer/bookings/'.$booking->id,
                $booking->id,
            );
        }

        if ($by === 'customer' && $booking->provider) {
            NotificationService::push(
                $booking->provider,
                'budget',
                'Customer updated budget on '.$booking->reference,
                'New budget: PKR '.number_format($amount),
                '/dashboard/provider/jobs/'.$booking->id,
                $booking->id,
            );
        }

        return response()->json([
            'message' => $by === 'provider'
                ? 'Quotation sent to customer.'
                : 'Budget updated. Waiting for provider quotation.',
            'booking' => $booking->toBookingArray(),
        ]);
    }

    public function acceptQuote(Request $request, FormSubmission $booking): JsonResponse
    {
        $user = $request->user();

        if (! $user->isCustomer() || $booking->user_id !== $user->id) {
            return response()->json(['message' => 'Only the customer can accept a provider quotation.'], 403);
        }

        if (! $booking->isBooking()) {
            return response()->json(['message' => 'Not a booking.'], 404);
        }

        if ($booking->status !== FormSubmission::STATUS_QUOTED
            || $booking->current_offer_by !== 'provider'
            || $booking->current_offer === null) {
            return response()->json(['message' => 'No provider quotation is waiting for acceptance.'], 422);
        }

        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $offers = $booking->offers ?? [];
        $offers[] = [
            'by' => 'customer',
            'amount' => (float) $booking->current_offer,
            'note' => $data['note'] ?? 'Customer accepted provider quotation',
            'at' => now()->toIso8601String(),
            'action' => 'accepted',
        ];

        $booking->update([
            'status' => FormSubmission::STATUS_CONFIRMED,
            'deal_amount' => $booking->current_offer,
            'deal_accepted_at' => now(),
            'offers' => $offers,
        ]);

        $booking->load(['user', 'provider']);

        if ($booking->provider) {
            NotificationService::push(
                $booking->provider,
                'deal',
                'Deal accepted on '.$booking->reference,
                'Customer accepted your quotation (PKR '.number_format((float) $booking->deal_amount).'). Chat is now open.',
                '/dashboard/provider/jobs/'.$booking->id,
                $booking->id,
            );
        }

        return response()->json([
            'message' => 'Deal finalized. Chat is unlocked and the provider can start the job.',
            'booking' => $booking->toBookingArray(),
        ]);
    }

    public function start(Request $request, FormSubmission $booking): JsonResponse
    {
        return $this->providerTransition(
            $request,
            $booking,
            FormSubmission::STATUS_CONFIRMED,
            FormSubmission::STATUS_IN_PROGRESS,
            ['started_at' => now()],
            'Job started.',
        );
    }

    public function complete(Request $request, FormSubmission $booking): JsonResponse
    {
        $user = $request->user();

        if (! $booking->isBooking()) {
            return response()->json(['message' => 'Not a booking.'], 404);
        }

        if (! $user->isCustomer() || $booking->user_id !== $user->id) {
            return response()->json(['message' => 'Only the customer can mark a job as completed.'], 403);
        }

        if ($booking->status !== FormSubmission::STATUS_IN_PROGRESS) {
            return response()->json(['message' => 'Job must be in progress before it can be completed.'], 422);
        }

        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $booking->update([
            'status' => FormSubmission::STATUS_COMPLETED,
            'completed_at' => now(),
            'provider_note' => $data['note'] ?? $booking->provider_note,
        ]);

        $booking->load(['user', 'provider']);

        if ($booking->provider) {
            NotificationService::push(
                $booking->provider,
                'completed',
                'Job completed · '.$booking->reference,
                'Customer marked this job as completed.',
                '/dashboard/provider/jobs/'.$booking->id,
                $booking->id,
            );
        }

        return response()->json([
            'message' => 'Job marked as completed.',
            'booking' => $booking->toBookingArray(),
        ]);
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
            FormSubmission::STATUS_QUOTED,
            FormSubmission::STATUS_CONFIRMED,
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

        if ($to === FormSubmission::STATUS_IN_PROGRESS && $booking->user) {
            NotificationService::push(
                $booking->user,
                'started',
                'Job started · '.$booking->reference,
                'Your provider has started the job.',
                '/dashboard/customer/bookings/'.$booking->id,
                $booking->id,
            );
        }

        return response()->json([
            'message' => $message,
            'booking' => $booking->toBookingArray(),
        ]);
    }
}
