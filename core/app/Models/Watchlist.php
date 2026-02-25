<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Watchlist extends Model
{
    protected $table = 'watchlist';
    
    protected $guarded = ['id'];

    protected $casts = [
        'notify_bid' => 'boolean',
        'notify_price_change' => 'boolean',
        'notify_ending' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}

