<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class GoogleSearchConsoleService
{
    public function isConfigured(): bool
    {
        $key = (string) Config::get('services.google_search_console.api_key', '');

        return $key !== '';
    }

    public function verifyPropertyAccessible(string $siteUrl): bool
    {
        $apiKey = (string) Config::get('services.google_search_console.api_key', '');
        if ($apiKey === '') {
            return false;
        }

        $trimmed = trim($siteUrl);
        if ($trimmed === '') {
            return false;
        }

        $encoded = rawurlencode($trimmed);
        $endpoint = 'https://www.googleapis.com/webmasters/v3/sites/' . $encoded;

        $response = Http::timeout(8)->get($endpoint, ['key' => $apiKey]);

        if ($response->successful()) {
            return true;
        }

        if ($response->status() === 404) {
            return false;
        }

        return false;
    }
}

