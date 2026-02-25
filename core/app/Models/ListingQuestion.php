<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ListingQuestion extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'answered_at' => 'datetime',
        'is_public' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function asker()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', Status::QUESTION_PENDING);
    }

    public function scopeAnswered($query)
    {
        return $query->where('status', Status::QUESTION_ANSWERED);
    }

    public function scopeHidden($query)
    {
        return $query->where('status', Status::QUESTION_HIDDEN);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Accessors
    public function questionStatus(): Attribute
    {
        return new Attribute(
            get: function () {
                $statusClasses = [
                    Status::QUESTION_PENDING => ['badge--warning', 'Pending'],
                    Status::QUESTION_ANSWERED => ['badge--success', 'Answered'],
                    Status::QUESTION_HIDDEN => ['badge--dark', 'Hidden'],
                ];

                $class = $statusClasses[$this->status] ?? ['badge--secondary', 'Unknown'];
                return '<span class="badge ' . $class[0] . '">' . trans($class[1]) . '</span>';
            }
        );
    }
}

