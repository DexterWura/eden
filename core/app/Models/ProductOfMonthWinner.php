<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOfMonthWinner extends Model
{
    protected $fillable = [
        'award_month',
        'startup_id',
        'upvote_count',
        'notified_at',
    ];

    protected $casts = [
        'award_month' => 'date',
        'notified_at' => 'datetime',
    ];

    public function startup(): BelongsTo
    {
        return $this->belongsTo(Startup::class);
    }
}
