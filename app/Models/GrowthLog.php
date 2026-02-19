<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowthLog extends Model
{
    protected $fillable = ['startup_id', 'event_type', 'points_added'];

    protected function casts(): array
    {
        return [
            'points_added' => 'integer',
        ];
    }

    public function startup(): BelongsTo
    {
        return $this->belongsTo(Startup::class);
    }
}
