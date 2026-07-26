<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = AppNotification::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->limit(40)
            ->get()
            ->map(fn (AppNotification $n) => [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'body' => $n->body,
                'link' => $n->link,
                'booking_id' => $n->booking_id,
                'read_at' => $n->read_at,
                'created_at' => $n->created_at,
            ]);

        $unread = AppNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'data' => $items,
            'unread' => $unread,
        ]);
    }

    public function markRead(Request $request, AppNotification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (! $notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json(['notification' => $notification]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        AppNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
