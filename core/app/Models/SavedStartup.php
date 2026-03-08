<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedStartup extends Model
{
    protected $table = 'saved_startups';

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
