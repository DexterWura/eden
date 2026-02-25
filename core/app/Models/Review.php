<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Review extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function escrow()
    {
        return $this->belongsTo(Escrow::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewedUser()
    {
        return $this->belongsTo(User::class, 'reviewed_user_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', Status::REVIEW_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', Status::REVIEW_APPROVED);
    }

    public function scopeHidden($query)
    {
        return $query->where('status', Status::REVIEW_HIDDEN);
    }

    public function scopeBuyerReviews($query)
    {
        return $query->where('review_type', 'buyer_review');
    }

    public function scopeSellerReviews($query)
    {
        return $query->where('review_type', 'seller_review');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('reviewed_user_id', $userId);
    }

    // Accessors
    public function reviewStatus(): Attribute
    {
        return new Attribute(
            get: function () {
                $statusClasses = [
                    Status::REVIEW_PENDING => ['badge--warning', 'Pending'],
                    Status::REVIEW_APPROVED => ['badge--success', 'Approved'],
                    Status::REVIEW_HIDDEN => ['badge--dark', 'Hidden'],
                ];

                $class = $statusClasses[$this->status] ?? ['badge--secondary', 'Unknown'];
                return '<span class="badge ' . $class[0] . '">' . trans($class[1]) . '</span>';
            }
        );
    }

    public function averageRating(): Attribute
    {
        return new Attribute(
            get: function () {
                $ratings = array_filter([
                    $this->overall_rating,
                    $this->communication_rating,
                    $this->accuracy_rating,
                    $this->timeliness_rating,
                ]);
                return count($ratings) > 0 ? array_sum($ratings) / count($ratings) : 0;
            }
        );
    }
}

