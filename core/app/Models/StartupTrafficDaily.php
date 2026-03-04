<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StartupTrafficDaily extends Model
{
    protected $table = 'startup_traffic_daily';

    protected $fillable = ['startup_id', 'date', 'visits'];

    protected $casts = [
        'date' => 'date',
    ];

    public function startup()
    {
        return $this->belongsTo(Startup::class);
    }
}
