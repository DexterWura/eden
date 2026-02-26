<?php

namespace App\Services;

class RobotsTxtService
{
    /**
     * Recommended robots.txt for Eden: allow public pages, disallow admin/founder/auth/API.
     */
    public static function recommendedContent(): string
    {
        $baseUrl = rtrim(url('/'), '/');

        $lines = [
            '# Eden – recommended robots.txt',
            '# Public pages are allowed; dashboards, auth, and API are disallowed.',
            '',
            'User-agent: *',
            'Allow: /',
            'Allow: /about',
            'Allow: /contact',
            'Allow: /submit',
            'Allow: /categories',
            'Allow: /launching-today',
            'Allow: /leaderboard',
            'Allow: /startup/',
            'Allow: /sitemap.xml',
            '',
            'Disallow: /backoffice',
            'Disallow: /founder',
            'Disallow: /admin',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /auth/',
            'Disallow: /api/',
            'Disallow: /startup/*/claim',
            'Disallow: /startup/*/claim/',
            '',
            'Sitemap: ' . $baseUrl . '/sitemap.xml',
            '',
        ];

        return implode("\n", $lines);
    }

    /**
     * Write robots.txt to public path. Returns path on success.
     */
    public function writeToPublic(string $content, ?string $path = null): string
    {
        $path = $path ?? public_path('robots.txt');
        \Illuminate\Support\Facades\File::put($path, trim($content) . "\n");
        return $path;
    }
}
