<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Service for verifying Envato/CodeCanyon purchases via Envato API.
 * 
 * This service handles purchase code verification using the Envato API
 * to ensure only legitimate buyers can install the system.
 */
class EnvatoVerificationService
{
    /**
     * Envato API base URL.
     */
    private const API_BASE_URL = 'https://api.envato.com/v3';

    /**
     * Cache duration for verification results (in minutes).
     */
    private const CACHE_DURATION = 60;

    /**
     * Verify a purchase code with Envato API.
     * 
     * @param string $purchaseCode The purchase code from CodeCanyon
     * @param string $username The Envato username
     * @param string|null $itemId Optional CodeCanyon item ID (can be configured)
     * @return array Result array with 'valid', 'message', and optional 'data'
     */
    public function verifyPurchase(string $purchaseCode, string $username, ?string $itemId = null): array
    {
        // Validate inputs
        if (empty($purchaseCode) || empty($username)) {
            return [
                'valid' => false,
                'message' => 'Purchase code and username are required.',
            ];
        }

        // Check cache first
        $cacheKey = 'envato_verification_' . md5($purchaseCode . $username);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            // Get personal token from config (should be set in .env)
            $personalToken = config('services.envato.personal_token');
            
            if (empty($personalToken)) {
                Log::error('Envato personal token not configured');
                return [
                    'valid' => false,
                    'message' => 'License verification service is not properly configured. Please contact support.',
                ];
            }

            // Call Envato API to verify purchase
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $personalToken,
                'User-Agent' => 'CodeCanyon Purchase Verifier',
            ])->timeout(30)->get(self::API_BASE_URL . '/market/buyer/purchase', [
                'code' => $purchaseCode,
            ]);

            if (!$response->successful()) {
                $errorMessage = 'Failed to verify purchase code.';
                
                if ($response->status() === 401) {
                    $errorMessage = 'Invalid API credentials. Please contact support.';
                } elseif ($response->status() === 404) {
                    $errorMessage = 'Purchase code not found or invalid.';
                } elseif ($response->status() === 429) {
                    $errorMessage = 'Too many verification requests. Please try again later.';
                }

                Log::warning('Envato API verification failed', [
                    'status' => $response->status(),
                    'purchase_code' => substr($purchaseCode, 0, 10) . '...',
                ]);

                return [
                    'valid' => false,
                    'message' => $errorMessage,
                ];
            }

            $data = $response->json();

            // Check if purchase exists
            if (!isset($data['purchase']) || empty($data['purchase'])) {
                return [
                    'valid' => false,
                    'message' => 'Purchase code not found or invalid.',
                ];
            }

            $purchase = $data['purchase'];

            // Verify username matches
            $buyerUsername = $purchase['buyer'] ?? null;
            if (empty($buyerUsername) || strtolower($buyerUsername) !== strtolower($username)) {
                return [
                    'valid' => false,
                    'message' => 'Purchase code does not belong to the provided username.',
                ];
            }

            // Verify item ID if provided
            if ($itemId !== null) {
                $purchaseItemId = $purchase['item']['id'] ?? null;
                if ($purchaseItemId != $itemId) {
                    return [
                        'valid' => false,
                        'message' => 'Purchase code is not for this product.',
                    ];
                }
            }

            // Check license type (Regular or Extended)
            $licenseType = $purchase['license'] ?? 'regular';
            $supportedLicenses = ['regular', 'extended'];
            if (!in_array(strtolower($licenseType), $supportedLicenses)) {
                return [
                    'valid' => false,
                    'message' => 'Unsupported license type.',
                ];
            }

            // Check if purchase is supported (not refunded, etc.)
            $supported = $purchase['supported_until'] ?? null;
            if ($supported && strtotime($supported) < time()) {
                return [
                    'valid' => false,
                    'message' => 'Purchase support has expired.',
                ];
            }

            $result = [
                'valid' => true,
                'message' => 'Purchase code verified successfully.',
                'data' => [
                    'purchase_code' => $purchaseCode,
                    'username' => $username,
                    'item_id' => $purchase['item']['id'] ?? null,
                    'item_name' => $purchase['item']['name'] ?? null,
                    'license' => $licenseType,
                    'purchase_date' => $purchase['sold_at'] ?? null,
                    'supported_until' => $supported,
                ],
            ];

            // Cache successful verification
            Cache::put($cacheKey, $result, now()->addMinutes(self::CACHE_DURATION));

            return $result;

        } catch (\Exception $e) {
            Log::error('Envato verification exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'valid' => false,
                'message' => 'An error occurred while verifying the purchase code. Please try again later.',
            ];
        }
    }

    /**
     * Clear cached verification result.
     * 
     * @param string $purchaseCode
     * @param string $username
     * @return void
     */
    public function clearCache(string $purchaseCode, string $username): void
    {
        $cacheKey = 'envato_verification_' . md5($purchaseCode . $username);
        Cache::forget($cacheKey);
    }
}
