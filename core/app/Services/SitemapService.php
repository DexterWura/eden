<?php

namespace App\Services;

use App\Models\Startup;
use Illuminate\Support\Facades\File;

class SitemapService
{
    public function generate(string $path = null): string
    {
        $path = $path ?? public_path('sitemap.xml');
        $baseUrl = rtrim(url('/'), '/');
        $urls = [];

        $static = [
            ['loc' => $baseUrl . '/', 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => $baseUrl . '/about', 'changefreq' => 'monthly', 'priority' => '0.8'],
            ['loc' => $baseUrl . '/contact', 'changefreq' => 'monthly', 'priority' => '0.7'],
            ['loc' => $baseUrl . '/submit', 'changefreq' => 'monthly', 'priority' => '0.8'],
            ['loc' => $baseUrl . '/categories', 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => $baseUrl . '/launching-today', 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => $baseUrl . '/leaderboard', 'changefreq' => 'daily', 'priority' => '0.9'],
        ];

        $now = now()->toAtomString();
        foreach ($static as $entry) {
            $urls[] = [
                'loc' => $entry['loc'],
                'lastmod' => $now,
                'changefreq' => $entry['changefreq'],
                'priority' => $entry['priority'],
            ];
        }

        $startups = Startup::active()->orderBy('updated_at')->get(['slug', 'updated_at']);
        foreach ($startups as $startup) {
            $urls[] = [
                'loc' => $baseUrl . '/startup/' . $startup->slug,
                'lastmod' => $startup->updated_at?->toAtomString() ?? $now,
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        $xml = $this->buildXml($urls);
        File::put($path, $xml);

        return $path;
    }

    private function buildXml(array $urls): string
    {
        $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $out .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $out .= '  <url>' . "\n";
            $out .= '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1, 'UTF-8') . '</loc>' . "\n";
            $out .= '    <lastmod>' . ($u['lastmod'] ?? now()->toAtomString()) . '</lastmod>' . "\n";
            if (! empty($u['changefreq'])) {
                $out .= '    <changefreq>' . htmlspecialchars($u['changefreq'], ENT_XML1, 'UTF-8') . '</changefreq>' . "\n";
            }
            if (isset($u['priority'])) {
                $out .= '    <priority>' . $u['priority'] . '</priority>' . "\n";
            }
            $out .= '  </url>' . "\n";
        }
        $out .= '</urlset>';

        return $out;
    }
}
