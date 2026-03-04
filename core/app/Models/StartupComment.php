<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StartupComment extends Model
{
    protected $fillable = ['startup_id', 'user_id', 'body'];

    public function startup()
    {
        return $this->belongsTo(Startup::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
