<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Offer extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'countered_at' => 'datetime',
        'expires_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function escrow()
    {
        return $this->belongsTo(Escrow::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', Status::OFFER_PENDING);
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', Status::OFFER_ACCEPTED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', Status::OFFER_REJECTED);
    }

    public function scopeCountered($query)
    {
        return $query->where('status', Status::OFFER_COUNTERED);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', Status::OFFER_EXPIRED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', Status::OFFER_CANCELLED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', Status::OFFER_COMPLETED);
    }

    public function scopeByBuyer($query, $userId)
    {
        return $query->where('buyer_id', $userId);
    }

    public function scopeBySeller($query, $userId)
    {
        return $query->where('seller_id', $userId);
    }

    // Accessors
    public function offerStatus(): Attribute
    {
        return new Attribute(
            get: function () {
                $statusClasses = [
                    Status::OFFER_PENDING => ['badge--warning', 'Pending'],
                    Status::OFFER_ACCEPTED => ['badge--success', 'Accepted'],
                    Status::OFFER_REJECTED => ['badge--danger', 'Rejected'],
                    Status::OFFER_COUNTERED => ['badge--info', 'Countered'],
                    Status::OFFER_EXPIRED => ['badge--dark', 'Expired'],
                    Status::OFFER_CANCELLED => ['badge--secondary', 'Cancelled'],
                    Status::OFFER_COMPLETED => ['badge--primary', 'Completed'],
                ];

                $class = $statusClasses[$this->status] ?? ['badge--secondary', 'Unknown'];
                return '<span class="badge ' . $class[0] . '">' . trans($class[1]) . '</span>';
            }
        );
    }

    public function isExpired(): Attribute
    {
        return new Attribute(
            get: fn() => $this->expires_at && $this->expires_at->isPast()
        );
    }

    public function finalAmount(): Attribute
    {
        return new Attribute(
            get: fn() => $this->counter_amount > 0 ? $this->counter_amount : $this->amount
        );
    }
}

