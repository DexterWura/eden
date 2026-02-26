<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StartupClaimVerification extends Model
{
    public const METHOD_DNS = 'dns';
    public const METHOD_FILE = 'file';

    protected $fillable = [
        'startup_id',
        'user_id',
        'method',
        'verification_code',
        'verification_file_name',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function startup(): BelongsTo
    {
        return $this->belongsTo(Startup::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }
}
