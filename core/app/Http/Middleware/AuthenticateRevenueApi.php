<?php

namespace App\Http\Middleware;

use App\Models\Startup;
use App\Models\StartupRevenueApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateRevenueApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->getToken($request);

        if ($token === null || $token === '') {
            return response()->json([
                'error' => 'unauthorized',
                'message' => 'Missing or invalid API key. Use Authorization: Bearer <your_api_key> or X-Eden-API-Key header.',
            ], 401, ['Content-Type' => 'application/json']);
        }

        $startup = StartupRevenueApiKey::findStartupByToken($token);

        if ($startup === null) {
            return response()->json([
                'error' => 'unauthorized',
                'message' => 'Invalid or revoked API key.',
            ], 401, ['Content-Type' => 'application/json']);
        }

        if ($startup->status !== Startup::STATUS_ACTIVE) {
            return response()->json([
                'error' => 'forbidden',
                'message' => 'App is not active. Revenue cannot be recorded.',
            ], 403, ['Content-Type' => 'application/json']);
        }

        StartupRevenueApiKey::where('token_hash', StartupRevenueApiKey::hashToken($token))
            ->update(['last_used_at' => now()]);

        $request->attributes->set('eden_revenue_startup', $startup);

        return $next($request);
    }

    private function getToken(Request $request): ?string
    {
        $header = $request->header('Authorization');
        if (is_string($header) && preg_match('/^\s*Bearer\s+(.+)\s*$/i', $header, $m)) {
            return trim($m[1]);
        }
        $key = $request->header('X-Eden-API-Key');
        if (is_string($key)) {
            return trim($key);
        }
        return null;
    }
}
