<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Startup;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $base = rtrim(config('app.url'), '/');
        $urls = [];

        $urls[] = ['loc' => $base . '/', 'changefreq' => 'daily', 'priority' => '1.0'];
        $urls[] = ['loc' => $base . '/startups', 'changefreq' => 'daily', 'priority' => '0.9'];
        $urls[] = ['loc' => $base . '/blog', 'changefreq' => 'daily', 'priority' => '0.8'];

        BlogPost::published()->select('slug', 'published_at')->orderBy('published_at')->chunk(500, function ($posts) use ($base, &$urls) {
            foreach ($posts as $p) {
                $urls[] = [
                    'loc' => $base . '/blog/' . $p->slug,
                    'lastmod' => $p->published_at?->toAtomString(),
                    'changefreq' => 'monthly',
                    'priority' => '0.6',
                ];
            }
        });

        Startup::approved()->select('slug', 'updated_at')->orderBy('updated_at')->chunk(500, function ($startups) use ($base, &$urls) {
            foreach ($startups as $s) {
                $urls[] = [
                    'loc' => $base . '/startups/' . $s->slug,
                    'lastmod' => $s->updated_at?->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                ];
            }
        });

        Category::select('slug', 'updated_at')->orderBy('slug')->chunk(200, function ($categories) use ($base, &$urls) {
            foreach ($categories as $c) {
                $urls[] = [
                    'loc' => $base . '/category/' . $c->slug,
                    'lastmod' => $c->updated_at?->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ];
            }
        });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= '  <url>';
            $xml .= '<loc>' . htmlspecialchars($u['loc']) . '</loc>';
            if (! empty($u['lastmod'])) {
                $xml .= '<lastmod>' . htmlspecialchars($u['lastmod']) . '</lastmod>';
            }
            $xml .= '<changefreq>' . ($u['changefreq'] ?? 'weekly') . '</changefreq>';
            $xml .= '<priority>' . ($u['priority'] ?? '0.5') . '</priority>';
            $xml .= '</url>' . "\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
