<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CofounderInvitation extends Model
{
    protected $fillable = [
        'startup_id',
        'invited_by_user_id',
        'accepted_by_user_id',
        'email',
        'token_hash',
        'delivery_token',
        'pending_key',
        'expires_at',
        'email_sent_at',
        'accepted_at',
    ];

    protected $hidden = ['token_hash', 'delivery_token'];

    protected $casts = [
        'delivery_token' => 'encrypted',
        'expires_at' => 'datetime',
        'email_sent_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function startup()
    {
        return $this->belongsTo(Startup::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function acceptedBy()
    {
        return $this->belongsTo(User::class, 'accepted_by_user_id');
    }

    public function isUsable(): bool
    {
        return $this->accepted_at === null && $this->expires_at->isFuture();
    }
}
