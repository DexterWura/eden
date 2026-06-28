<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class StartupWebsiteHealthService
{
    public const CHECK_INTERVAL_DAYS = 3;

    public const CONSECUTIVE_FAILURES_BEFORE_DORMANT = 6;

    public const DORMANT_DAYS_BEFORE_DELETE = 30;

    private const PING_TIMEOUT_SECONDS = 30;

    private const CONNECT_TIMEOUT_SECONDS = 15;

    private const MAX_ATTEMPTS_PER_URL = 3;

    private const BROWSER_USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36';

    public function normalizeUrl(?string $website): ?string
    {
        if ($website === null || trim($website) === '') {
            return null;
        }

        $url = trim($website);
        if (! preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        return $url;
    }

    /**
     * @return array<int, string>
     */
    public function urlVariants(string $url): array
    {
        $variants = [$url];
        $parts = parse_url($url);

        if ($parts === false || empty($parts['host'])) {
            return $variants;
        }

        $scheme = strtolower($parts['scheme'] ?? 'https');
        $host = strtolower($parts['host']);
        $path = $parts['path'] ?? '';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';

        $hosts = [$host];
        if (str_starts_with($host, 'www.')) {
            $hosts[] = substr($host, 4);
        } else {
            $hosts[] = 'www.' . $host;
        }

        foreach (array_unique($hosts) as $candidateHost) {
            foreach (['https', 'http'] as $candidateScheme) {
                $variants[] = $candidateScheme . '://' . $candidateHost . $port . $path . $query;
            }
        }

        if ($path === '' || $path === '/') {
            foreach ($variants as $variant) {
                $variants[] = rtrim($variant, '/') . '/';
            }
        }

        return array_values(array_unique($variants));
    }

    public function isUrlReachable(string $url): bool
    {
        foreach ($this->urlVariants($url) as $variant) {
            if ($this->requestUrl($variant)) {
                return true;
            }
        }

        return false;
    }

    private function requestUrl(string $url): bool
    {
        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS_PER_URL; $attempt++) {
            if ($attempt > 0) {
                sleep(2 * $attempt);
            }

            foreach (['GET', 'HEAD'] as $method) {
                try {
                    $request = Http::timeout(self::PING_TIMEOUT_SECONDS)
                        ->connectTimeout(self::CONNECT_TIMEOUT_SECONDS)
                        ->withOptions([
                            'verify' => false,
                            'allow_redirects' => [
                                'max' => 5,
                                'strict' => false,
                                'referer' => true,
                                'protocols' => ['http', 'https'],
                            ],
                        ])
                        ->withHeaders([
                            'User-Agent' => self::BROWSER_USER_AGENT,
                            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                            'Accept-Language' => 'en-US,en;q=0.9',
                        ]);

                    $response = $method === 'HEAD'
                        ? $request->head($url)
                        : $request->get($url);

                    if ($this->isReachableStatusCode($response->status())) {
                        return true;
                    }
                } catch (\Throwable) {
                    continue;
                }
            }
        }

        return false;
    }

    /**
     * Treat any meaningful HTTP response as alive — many live sites return 403/401 to bots.
     */
    private function isReachableStatusCode(int $status): bool
    {
        return $status >= 200 && $status < 500;
    }
}
