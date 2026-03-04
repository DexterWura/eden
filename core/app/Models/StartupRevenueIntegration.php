<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class StartupRevenueIntegration extends Model
{
    public const GATEWAY_STRIPE = 'stripe';
    public const GATEWAY_POLAR = 'polar';
    public const GATEWAY_LEMONSQUEEZY = 'lemonsqueezy';

    public const GATEWAYS = [
        self::GATEWAY_STRIPE => ['name' => 'Stripe', 'icon' => 'fa-brands fa-stripe', 'docs' => 'https://dashboard.stripe.com/apikeys'],
        self::GATEWAY_POLAR => ['name' => 'Polar', 'icon' => 'fa-solid fa-snowflake', 'docs' => 'https://polar.sh/settings/api'],
        self::GATEWAY_LEMONSQUEEZY => ['name' => 'Lemon Squeezy', 'icon' => 'fa-solid fa-lemon', 'docs' => 'https://app.lemonsqueezy.com/settings/api'],
    ];

    protected $fillable = ['startup_id', 'gateway', 'credentials', 'last_synced_at', 'last_sync_status', 'settings'];

    protected $casts = [
        'last_synced_at' => 'datetime',
        'settings' => 'array',
    ];

    public function startup()
    {
        return $this->belongsTo(Startup::class);
    }

    public function getDecryptedCredentials(): array
    {
        try {
            return json_decode(Crypt::decryptString($this->credentials), true) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function setCredentials(array $creds): void
    {
        $this->credentials = Crypt::encryptString(json_encode($creds));
        $this->save();
    }

    public function getApiKey(): ?string
    {
        return $this->getDecryptedCredentials()['api_key'] ?? null;
    }
}
