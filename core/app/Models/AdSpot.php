<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AdSpot extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'placement',
        'image_path',
        'target_url',
        'status',
        'starts_at',
        'ends_at',
        'contact_email',
        'payment_reference',
        'gateway',
        'amount',
        'currency',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function scopeActiveForPlacement(Builder $query, string $placement): Builder
    {
        return $query
            ->where('placement', $placement)
            ->where('status', self::STATUS_ACTIVE)
            ->whereNotNull('starts_at')
            ->whereNotNull('ends_at')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->starts_at
            && $this->ends_at
            && $this->starts_at->isPast()
            && $this->ends_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED
            || ($this->ends_at && $this->ends_at->isPast());
    }
}

