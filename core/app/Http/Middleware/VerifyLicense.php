<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\LicenseEncryptionService;
use App\Services\EnvatoVerificationService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to verify license periodically.
 * 
 * This middleware checks the license validity on admin routes
 * and prevents access if the license is invalid or expired.
 */
class VerifyLicense
{
    /**
     * Cache key for license check.
     */
    private const CACHE_KEY = 'license_last_check';

    /**
     * How often to check license (in hours).
     */
    private const CHECK_INTERVAL_HOURS = 24;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip license check in local/development environment
        if (config('app.env') === 'local' || config('app.debug') === true) {
            return $next($request);
        }

        // Skip if installer is running
        if ($request->is('install*')) {
            return $next($request);
        }

        try {
            $licenseEncryptionService = app(LicenseEncryptionService::class);
            
            // Check if license file exists
            if (!$licenseEncryptionService->licenseExists()) {
                Log::warning('License file not found');
                return $this->handleInvalidLicense($request);
            }

            // Check cache to avoid checking too frequently
            $lastCheck = Cache::get(self::CACHE_KEY);
            $shouldCheck = !$lastCheck || 
                (now()->diffInHours($lastCheck) >= self::CHECK_INTERVAL_HOURS);

            if ($shouldCheck) {
                $licenseData = $licenseEncryptionService->retrieveLicense();
                
                if (empty($licenseData)) {
                    Log::warning('Failed to retrieve license data');
                    return $this->handleInvalidLicense($request);
                }

                // Verify with Envato API
                $envatoService = app(EnvatoVerificationService::class);
                $verificationResult = $envatoService->verifyPurchase(
                    $licenseData['purchase_code'] ?? '',
                    $licenseData['username'] ?? '',
                    $licenseData['item_id'] ?? null
                );

                // Update cache
                Cache::put(self::CACHE_KEY, now(), now()->addHours(self::CHECK_INTERVAL_HOURS));

                if (!$verificationResult['valid']) {
                    Log::warning('License verification failed', [
                        'message' => $verificationResult['message'] ?? 'Unknown error',
                    ]);
                    return $this->handleInvalidLicense($request, $verificationResult['message'] ?? 'License verification failed');
                }
            }

            return $next($request);

        } catch (\Exception $e) {
            Log::error('License verification middleware error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Allow request to proceed if it's a temporary error
            // But log it for investigation
            return $next($request);
        }
    }

    /**
     * Handle invalid license scenario.
     * 
     * @param Request $request
     * @param string|null $message
     * @return Response
     */
    private function handleInvalidLicense(Request $request, ?string $message = null): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'remark' => 'license_invalid',
                'status' => 'error',
                'message' => ['error' => [$message ?? 'License verification failed. Please contact support.']],
            ], 403);
        }

        // For web requests, show error page
        abort(403, $message ?? 'License verification failed. Please contact support.');
    }
}
