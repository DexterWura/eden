<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaunchNotification extends Model
{
    protected $fillable = ['email', 'startup_id'];

    public function startup()
    {
        return $this->belongsTo(Startup::class);
    }
}
