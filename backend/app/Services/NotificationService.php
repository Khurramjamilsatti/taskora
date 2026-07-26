<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;

class NotificationService
{
    public static function push(
        User $user,
        string $type,
        string $title,
        ?string $body = null,
        ?string $link = null,
        ?int $bookingId = null,
    ): AppNotification {
        return AppNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'link' => $link,
            'booking_id' => $bookingId,
        ]);
    }
}
