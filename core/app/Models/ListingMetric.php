<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingMetric extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'period_date' => 'date',
        'is_verified' => 'boolean',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function scopeMonthly($query)
    {
        return $query->where('period_type', 'monthly');
    }

    public function scopeWeekly($query)
    {
        return $query->where('period_type', 'weekly');
    }

    public function scopeDaily($query)
    {
        return $query->where('period_type', 'daily');
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }
}

