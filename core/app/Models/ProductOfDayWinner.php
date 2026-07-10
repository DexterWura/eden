<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOfDayWinner extends Model
{
    protected $fillable = [
        'award_date',
        'startup_id',
        'upvote_count',
    ];

    protected $casts = [
        'award_date' => 'date',
    ];

    public function startup(): BelongsTo
    {
        return $this->belongsTo(Startup::class);
    }
}
