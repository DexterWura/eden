<?php

namespace App\Services;

use App\Models\MarketplaceSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DatafastService
{
    /**
     * Get DataFast API key from settings
     */
    protected static function getApiKey(): ?string
    {
        return MarketplaceSetting::getValue('datafast_api_key');
    }

    /**
     * Track a payment to DataFast
     * 
     * @param float $amount Payment amount
     * @param string $currency Currency code (e.g., "USD", "EUR", "GBP")
     * @param string $transactionId Unique transaction ID
     * @param array $options Optional fields:
     *   - datafast_visitor_id (string): DataFast visitor ID from browser cookies
     *   - email (string): Customer email
     *   - name (string): Customer name
     *   - customer_id (string): Customer ID
     *   - renewal (bool): Set to true if it's a recurring payment
     *   - refunded (bool): Set to true if it's a refunded payment
     *   - timestamp (string): Payment timestamp (defaults to now)
     * 
     * @return array|null Returns response data on success, null on failure
     */
    public static function trackPayment(float $amount, string $currency, string $transactionId, array $options = []): ?array
    {
        $apiKey = self::getApiKey();
        
        if (!$apiKey) {
            Log::warning('DataFast API key not configured. Skipping payment tracking.', [
                'transaction_id' => $transactionId,
                'amount' => $amount,
            ]);
            return null;
        }

        try {
            $payload = [
                'amount' => $amount,
                'currency' => strtoupper($currency),
                'transaction_id' => $transactionId,
            ];

            // Add optional fields
            if (isset($options['datafast_visitor_id'])) {
                $payload['datafast_visitor_id'] = $options['datafast_visitor_id'];
            }
            if (isset($options['email'])) {
                $payload['email'] = $options['email'];
            }
            if (isset($options['name'])) {
                $payload['name'] = $options['name'];
            }
            if (isset($options['customer_id'])) {
                $payload['customer_id'] = $options['customer_id'];
            }
            if (isset($options['renewal'])) {
                $payload['renewal'] = (bool) $options['renewal'];
            }
            if (isset($options['refunded'])) {
                $payload['refunded'] = (bool) $options['refunded'];
            }
            if (isset($options['timestamp'])) {
                $payload['timestamp'] = $options['timestamp'];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(10)->post('https://datafa.st/api/v1/payments', $payload);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('DataFast payment tracked successfully', [
                    'transaction_id' => $transactionId,
                    'datafast_response' => $data,
                ]);
                return $data;
            } else {
                Log::error('DataFast payment tracking failed', [
                    'transaction_id' => $transactionId,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('DataFast payment tracking exception', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get DataFast visitor ID from request cookies
     * 
     * @param \Illuminate\Http\Request|null $request
     * @return string|null
     */
    public static function getVisitorId($request = null): ?string
    {
        if (!$request) {
            $request = request();
        }

        if (!$request) {
            return null;
        }

        // DataFast stores visitor ID in cookies as 'datafast_visitor_id'
        return $request->cookie('datafast_visitor_id');
    }
}
