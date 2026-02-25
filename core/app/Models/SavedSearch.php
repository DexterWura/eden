<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedSearch extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'filters' => 'array',
        'email_alerts' => 'boolean',
        'last_alerted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeWithAlerts($query)
    {
        return $query->where('email_alerts', true);
    }

    public function scopeInstant($query)
    {
        return $query->where('alert_frequency', 'instant');
    }

    public function scopeDaily($query)
    {
        return $query->where('alert_frequency', 'daily');
    }

    public function scopeWeekly($query)
    {
        return $query->where('alert_frequency', 'weekly');
    }
}

