<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\Startup;
use Illuminate\Support\Facades\Http;

class ClaimVerificationService
{
    public function verify(Claim $claim): bool
    {
        $startup = $claim->startup;
        $url = $startup->url;
        if (! $url || ! str_starts_with(strtolower($url), 'http')) {
            $url = 'https://' . ltrim($url, '/');
        }

        try {
            $response = Http::timeout(10)->get($url);
            if (! $response->successful()) {
                return false;
            }
            $html = $response->body();
            $token = $claim->verification_token;
            $pattern = '/<meta\s+name=["\']eden-verification["\']\s+content=["\']' . preg_quote($token, '/') . '["\']/i';
            if (preg_match($pattern, $html)) {
                $claim->update(['verified_at' => now()]);
                $startup->update(['user_id' => $claim->user_id, 'last_updated_at' => now()]);
                app(StartupGrowthService::class)->recalculateStatus($startup);
                return true;
            }
        } catch (\Throwable $e) {
            return false;
        }

        return false;
    }
}
