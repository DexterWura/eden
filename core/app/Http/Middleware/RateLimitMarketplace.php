<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMarketplace
{
    /**
     * Handle an incoming request with marketplace-specific rate limiting
     */
    public function handle(Request $request, Closure $next, string $type = 'general', int $maxAttempts = 60, int $decayMinutes = 1): Response
    {
        $user = auth()->user();
        $ip = $request->ip();

        // Use user ID if authenticated, otherwise IP address
        $identifier = $user ? 'user_' . $user->id : 'ip_' . str_replace(['.', ':'], '_', $ip);

        $key = "marketplace_rate_limit:{$type}:{$identifier}";
        $attempts = Cache::get($key, 0);

        // Check if limit exceeded
        if ($attempts >= $maxAttempts) {
            Log::warning("Rate limit exceeded for {$type}", [
                'identifier' => $identifier,
                'attempts' => $attempts,
                'max_attempts' => $maxAttempts,
                'ip' => $ip,
                'user_id' => $user->id ?? null,
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many requests. Please wait and try again.',
                    'error_type' => 'RATE_LIMITED',
                    'retry_after' => Cache::get("{$key}_retry_after")
                ], 429);
            }

            return response()->view('errors.429', [
                'message' => 'Too many requests. Please wait and try again.',
                'retry_after' => Cache::get("{$key}_retry_after")
            ], 429);
        }

        // Increment attempts
        Cache::put($key, $attempts + 1, now()->addMinutes($decayMinutes));

        // Set retry after time
        Cache::put("{$key}_retry_after", now()->addMinutes($decayMinutes)->timestamp, now()->addMinutes($decayMinutes));

        $response = $next($request);

        return $response;
    }
}
