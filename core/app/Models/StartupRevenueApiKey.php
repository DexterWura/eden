<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StartupRevenueApiKey extends Model
{
    protected $fillable = ['startup_id', 'token_hash', 'name'];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    public function startup()
    {
        return $this->belongsTo(Startup::class);
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public static function generateToken(): string
    {
        return 'eden_' . Str::random(48);
    }

    public static function findStartupByToken(string $token): ?Startup
    {
        $hash = self::hashToken($token);
        $key = self::where('token_hash', $hash)->first();

        return $key?->startup;
    }
}
