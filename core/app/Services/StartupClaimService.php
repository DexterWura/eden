<?php

namespace App\Services;

use App\Models\Startup;
use App\Models\StartupClaimVerification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class StartupClaimService
{
    public const DNS_RECORD_NAME = 'eden-verification';

    public function generateVerificationCode(): string
    {
        return Str::random(32);
    }

    public function generateFileName(): string
    {
        return 'eden-verify-' . Str::random(16) . '.txt';
    }

    public function getDomainFromWebsite(?string $website): ?string
    {
        if ($website === null || $website === '') {
            return null;
        }
        $parsed = parse_url($website);
        $host = $parsed['host'] ?? null;
        if ($host === null) {
            return null;
        }
        $host = strtolower($host);
        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }
        return $host;
    }

    public function getBaseUrl(?string $website): ?string
    {
        if ($website === null || $website === '') {
            return null;
        }
        $parsed = parse_url($website);
        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'] ?? null;
        if ($host === null) {
            return null;
        }
        $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
        return $scheme . '://' . $host . $port;
    }

    public function verifyDns(Startup $startup, string $code): bool
    {
        $domain = $this->getDomainFromWebsite($startup->website);
        if ($domain === null) {
            return false;
        }
        $records = @dns_get_record($domain, DNS_TXT);
        if ($records === false || ! is_array($records)) {
            return false;
        }
        $expected = self::DNS_RECORD_NAME . '=' . $code;
        foreach ($records as $record) {
            $txt = $record['txt'] ?? null;
            if (is_string($txt) && trim($txt) === $expected) {
                return true;
            }
            if (is_array($txt)) {
                foreach ($txt as $t) {
                    if (is_string($t) && trim($t) === $expected) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function verifyFile(Startup $startup, string $fileName, string $code): bool
    {
        $baseUrl = $this->getBaseUrl($startup->website);
        if ($baseUrl === null) {
            return false;
        }
        $url = rtrim($baseUrl, '/') . '/' . ltrim($fileName, '/');
        try {
            $response = Http::timeout(10)->get($url);
            if (! $response->successful()) {
                return false;
            }
            $body = trim($response->body());
            return $body === $code;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getDnsInstructions(string $code): array
    {
        return [
            'type' => 'TXT',
            'name' => '_eden-verification',
            'value' => self::DNS_RECORD_NAME . '=' . $code,
            'full_record' => self::DNS_RECORD_NAME . '=' . $code,
        ];
    }

    public function createVerification(Startup $startup, int $userId, string $method): StartupClaimVerification
    {
        StartupClaimVerification::where('startup_id', $startup->id)
            ->where('user_id', $userId)
            ->whereNull('verified_at')
            ->delete();

        $code = $this->generateVerificationCode();
        $fileName = $method === StartupClaimVerification::METHOD_FILE ? $this->generateFileName() : null;

        return StartupClaimVerification::create([
            'startup_id' => $startup->id,
            'user_id' => $userId,
            'method' => $method,
            'verification_code' => $code,
            'verification_file_name' => $fileName,
        ]);
    }
}
