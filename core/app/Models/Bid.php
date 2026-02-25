<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Bid extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_auto_bid' => 'boolean',
        'is_buy_now' => 'boolean',
    ];

    /**
     * Boot the model and add event listeners for data integrity
     */
    protected static function boot()
    {
        parent::boot();

        // Validate data before saving
        static::saving(function ($bid) {
            $bid->validateDataIntegrity();
        });
    }

    /**
     * Validate data integrity before saving
     */
    protected function validateDataIntegrity()
    {
        // Amount validation
        if ($this->amount <= 0) {
            throw new \InvalidArgumentException("Bid amount must be greater than 0");
        }

        // Max bid validation - only validate if max_bid is set and greater than 0
        // Note: 0 means no max bid was set, so we only validate when max_bid > 0
        if ($this->max_bid > 0 && $this->max_bid < $this->amount) {
            throw new \InvalidArgumentException("Maximum bid cannot be less than bid amount");
        }

        // Status validation
        $validStatuses = [0, 1, 2, 3, 4, 5]; // ACTIVE, OUTBID, WINNING, WON, LOST, CANCELLED
        if (!in_array($this->status, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid bid status");
        }

        // Prevent duplicate active bids from same user on same listing
        if ($this->isDirty() && in_array($this->status, [0, 2])) { // ACTIVE or WINNING
            $existingActiveBid = static::where('listing_id', $this->listing_id)
                ->where('user_id', $this->user_id)
                ->whereIn('status', [0, 2])
                ->where('id', '!=', $this->id ?? 0)
                ->exists();

            if ($existingActiveBid) {
                throw new \InvalidArgumentException("User already has an active bid on this listing");
            }
        }
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bidder()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', Status::BID_ACTIVE);
    }

    public function scopeOutbid($query)
    {
        return $query->where('status', Status::BID_OUTBID);
    }

    public function scopeWinning($query)
    {
        return $query->where('status', Status::BID_WINNING);
    }

    public function scopeWon($query)
    {
        return $query->where('status', Status::BID_WON);
    }

    public function scopeLost($query)
    {
        return $query->where('status', Status::BID_LOST);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', Status::BID_CANCELLED);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Accessors
    public function bidStatus(): Attribute
    {
        return new Attribute(
            get: function () {
                $statusClasses = [
                    Status::BID_ACTIVE => ['badge--info', 'Active'],
                    Status::BID_OUTBID => ['badge--warning', 'Outbid'],
                    Status::BID_WINNING => ['badge--success', 'Winning'],
                    Status::BID_WON => ['badge--primary', 'Won'],
                    Status::BID_LOST => ['badge--dark', 'Lost'],
                    Status::BID_CANCELLED => ['badge--danger', 'Cancelled'],
                ];

                $class = $statusClasses[$this->status] ?? ['badge--secondary', 'Unknown'];
                return '<span class="badge ' . $class[0] . '">' . trans($class[1]) . '</span>';
            }
        );
    }
}

