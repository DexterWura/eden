<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StartupRevenueEvent extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['startup_id', 'amount', 'currency', 'external_id', 'raw_payload'];

    protected $casts = [
        'amount' => 'decimal:2',
        'raw_payload' => 'array',
    ];

    public function startup()
    {
        return $this->belongsTo(Startup::class);
    }
}
