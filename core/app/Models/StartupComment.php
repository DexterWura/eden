<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StartupComment extends Model
{
    protected $fillable = [
        'startup_id',
        'user_id',
        'body',
        'founder_reply',
        'founder_replied_by',
        'founder_replied_at',
        'addressed_at',
    ];

    protected $casts = [
        'founder_replied_at' => 'datetime',
        'addressed_at' => 'datetime',
    ];

    public function startup()
    {
        return $this->belongsTo(Startup::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function founderResponder()
    {
        return $this->belongsTo(User::class, 'founder_replied_by');
    }
}
