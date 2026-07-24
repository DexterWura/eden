<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOfYearWinner extends Model
{
    protected $fillable = [
        'award_year',
        'startup_id',
        'upvote_count',
        'notified_at',
    ];

    protected $casts = [
        'award_year' => 'integer',
        'notified_at' => 'datetime',
    ];

    public function startup(): BelongsTo
    {
        return $this->belongsTo(Startup::class);
    }
}
