<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\BookingMessage;
use App\Models\FormSubmission;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    private function assertChatAccess(Request $request, FormSubmission $booking): ?JsonResponse
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

        if (! in_array($booking->status, [
            FormSubmission::STATUS_CONFIRMED,
            FormSubmission::STATUS_IN_PROGRESS,
            FormSubmission::STATUS_COMPLETED,
        ], true)) {
            return response()->json([
                'message' => 'Chat unlocks after the customer accepts the provider quotation.',
            ], 422);
        }

        return null;
    }

    public function index(Request $request, FormSubmission $booking): JsonResponse
    {
        if ($error = $this->assertChatAccess($request, $booking)) {
            return $error;
        }

        $messages = BookingMessage::query()
            ->with('user:id,name,role')
            ->where('booking_id', $booking->id)
            ->orderBy('created_at')
            ->limit(200)
            ->get()
            ->map(fn (BookingMessage $m) => [
                'id' => $m->id,
                'body' => $m->body,
                'created_at' => $m->created_at,
                'read_at' => $m->read_at,
                'mine' => $m->user_id === $request->user()->id,
                'user' => [
                    'id' => $m->user?->id,
                    'name' => $m->user?->name,
                    'role' => $m->user?->role,
                ],
            ]);

        // Mark counterpart messages as read
        BookingMessage::query()
            ->where('booking_id', $booking->id)
            ->where('user_id', '!=', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['data' => $messages]);
    }

    public function store(Request $request, FormSubmission $booking): JsonResponse
    {
        if ($error = $this->assertChatAccess($request, $booking)) {
            return $error;
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $user = $request->user();

        $message = BookingMessage::create([
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'body' => trim($data['body']),
        ]);

        $recipient = $user->isCustomer()
            ? $booking->provider
            : $booking->user;

        if ($recipient) {
            $link = $user->isCustomer()
                ? '/dashboard/provider/jobs/'.$booking->id
                : '/dashboard/customer/bookings/'.$booking->id;

            NotificationService::push(
                $recipient,
                'chat',
                'New message on '.$booking->reference,
                mb_strimwidth($message->body, 0, 120, '…'),
                $link,
                $booking->id,
            );
        }

        $message->load('user:id,name,role');

        return response()->json([
            'message' => [
                'id' => $message->id,
                'body' => $message->body,
                'created_at' => $message->created_at,
                'read_at' => $message->read_at,
                'mine' => true,
                'user' => [
                    'id' => $message->user?->id,
                    'name' => $message->user?->name,
                    'role' => $message->user?->role,
                ],
            ],
        ], 201);
    }
}
