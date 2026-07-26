<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmission extends Model
{
    public const STATUS_RECEIVED = 'received';

    public const STATUS_ASSIGNED = 'assigned';

    public const STATUS_QUOTED = 'quoted';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'provider_id',
        'type',
        'reference',
        'payload',
        'customer_budget',
        'current_offer',
        'current_offer_by',
        'deal_amount',
        'offers',
        'status',
        'provider_note',
        'accepted_at',
        'quoted_at',
        'deal_accepted_at',
        'started_at',
        'completed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'offers' => 'array',
            'customer_budget' => 'decimal:2',
            'current_offer' => 'decimal:2',
            'deal_amount' => 'decimal:2',
            'accepted_at' => 'datetime',
            'quoted_at' => 'datetime',
            'deal_accepted_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function isBooking(): bool
    {
        return $this->type === 'booking';
    }

    public function canNegotiate(): bool
    {
        return in_array($this->status, [
            self::STATUS_ASSIGNED,
            self::STATUS_QUOTED,
        ], true);
    }

    public function toBookingArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'reference' => $this->reference,
            'status' => $this->status,
            'payload' => $this->payload,
            'customer_budget' => $this->customer_budget,
            'current_offer' => $this->current_offer,
            'current_offer_by' => $this->current_offer_by,
            'deal_amount' => $this->deal_amount,
            'offers' => $this->offers ?? [],
            'provider_note' => $this->provider_note,
            'accepted_at' => $this->accepted_at,
            'quoted_at' => $this->quoted_at,
            'deal_accepted_at' => $this->deal_accepted_at,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
            'cancelled_at' => $this->cancelled_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'customer' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
            ] : null,
            'provider' => $this->provider ? [
                'id' => $this->provider->id,
                'name' => $this->provider->name,
                'email' => $this->provider->email,
                'phone' => $this->provider->phone,
            ] : null,
        ];
    }
}
