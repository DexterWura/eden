<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Startup;
use App\Support\StartupContentPolicy;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SitemapService
{
    public function generate(string $path = null): string
    {
        $path = $path ?? public_path('sitemap.xml');
        File::put($path, $this->render());

        return $path;
    }

    public function render(): string
    {
        return $this->buildXml($this->collectUrls());
    }

    private function collectUrls(): array
    {
        $baseUrl = rtrim(url('/'), '/');
        $urls = [];

        $static = [
            ['loc' => $baseUrl . '/', 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => $baseUrl . '/about', 'changefreq' => 'monthly', 'priority' => '0.8'],
            ['loc' => $baseUrl . '/contact', 'changefreq' => 'monthly', 'priority' => '0.7'],
            ['loc' => $baseUrl . '/privacy', 'changefreq' => 'monthly', 'priority' => '0.6'],
            ['loc' => $baseUrl . '/terms', 'changefreq' => 'monthly', 'priority' => '0.6'],
            ['loc' => $baseUrl . '/submit', 'changefreq' => 'monthly', 'priority' => '0.8'],
            ['loc' => $baseUrl . '/categories', 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => $baseUrl . '/launching-today', 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => $baseUrl . '/leaderboard', 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => $baseUrl . '/pricing', 'changefreq' => 'monthly', 'priority' => '0.8'],
            ['loc' => $baseUrl . '/raising', 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => $baseUrl . '/for-sale', 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => $baseUrl . '/blog', 'changefreq' => 'daily', 'priority' => '0.8'],
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

        $startups = Startup::active()
            ->orderBy('updated_at')
            ->get()
            ->filter(fn (Startup $startup) => $startup->shouldBeIndexed());
        foreach ($startups as $startup) {
            $urls[] = [
                'loc' => $baseUrl . '/startup/' . $startup->slug,
                'lastmod' => $startup->updated_at?->toAtomString() ?? $now,
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        $categories = Category::query()
            ->withCount(['startups' => fn ($query) => $query->active()])
            ->get()
            ->filter(fn (Category $category) => $category->startups_count > 0 && $category->hasEditorialDepth());
        foreach ($categories as $category) {
            $urls[] = [
                'loc' => $baseUrl . '/categories/' . $category->slug,
                'lastmod' => $category->updated_at?->toAtomString() ?? $now,
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        $locationGroups = Startup::query()
            ->active()
            ->whereNotNull('location')
            ->get()
            ->groupBy('location');
        foreach ($locationGroups as $location => $locationStartups) {
            $substantiveCount = $locationStartups
                ->filter(fn (Startup $startup) => $startup->hasSubstantiveContent())
                ->count();
            if (! StartupContentPolicy::locationHubIsIndexable($locationStartups->count(), $substantiveCount)) {
                continue;
            }
            $lastUpdated = $locationStartups->sortByDesc('updated_at')->first()?->updated_at;
            $urls[] = [
                'loc' => $baseUrl . '/locations/' . Str::slug($location),
                'lastmod' => $lastUpdated?->toAtomString() ?? $now,
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        $blogPosts = BlogPost::published()->orderBy('updated_at')->get(['slug', 'updated_at', 'published_at']);
        foreach ($blogPosts as $post) {
            $lastmod = $post->updated_at?->toAtomString() ?? $post->published_at?->toAtomString() ?? $now;
            $urls[] = [
                'loc' => $baseUrl . '/blog/' . $post->slug,
                'lastmod' => $lastmod,
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }

        return $urls;
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
