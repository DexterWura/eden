<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StartupUpvote extends Model
{
    protected $fillable = ['user_id', 'startup_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function startup()
    {
        return $this->belongsTo(Startup::class);
    }
}
