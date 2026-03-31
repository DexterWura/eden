<?php

namespace App\Support\Seo;

use Illuminate\Http\Request;

/**
 * Canonical URLs and robots meta for Eden public pages (avoid duplicate indexation).
 */
final class EdenSeo
{
    /**
     * Homepage: search result URLs are noindex; paginated browse is noindex with canonical to page 1 of filters.
     */
    public static function forHome(Request $request): array
    {
        $hasSearch = $request->filled('q') && trim((string) $request->query('q')) !== '';

        if ($hasSearch) {
            return [
                'canonicalUrl' => rtrim(url('/'), '/'),
                'metaRobots' => 'noindex,follow',
                'includeDefaultSiteGraph' => false,
            ];
        }

        $params = [];
        foreach (['category', 'location', 'featured', 'sort'] as $key) {
            if (! $request->has($key)) {
                continue;
            }
            $val = $request->query($key);
            if ($val === null || $val === '') {
                continue;
            }
            if ($key === 'featured' && (string) $val !== '1') {
                continue;
            }
            $params[$key] = $val;
        }
        ksort($params);

        $page = max(1, (int) $request->query('page', 1));

        $base = rtrim(url('/'), '/');
        $qs = http_build_query($params);
        $canonicalFirst = $qs === '' ? $base : $base . '?' . $qs;

        if ($page > 1) {
            return [
                'canonicalUrl' => $canonicalFirst,
                'metaRobots' => 'noindex,follow',
                'includeDefaultSiteGraph' => false,
            ];
        }

        return [
            'canonicalUrl' => $canonicalFirst,
            'metaRobots' => null,
            'includeDefaultSiteGraph' => true,
        ];
    }

    public static function forLeaderboard(Request $request): array
    {
        $sortBy = $request->query('sort', 'upvotes');
        if (! in_array($sortBy, ['upvotes', 'views', 'clicks', 'mrr', 'revenue', 'newest'], true)) {
            $sortBy = 'upvotes';
        }
        $location = $request->query('location');

        $params = [];
        if ($sortBy !== 'upvotes') {
            $params['sort'] = $sortBy;
        }
        if ($location !== null && trim((string) $location) !== '') {
            $params['location'] = $location;
        }
        ksort($params);

        $page = max(1, (int) $request->query('page', 1));

        $base = rtrim(url('/leaderboard'), '/');
        $qs = http_build_query($params);
        $canonicalFirst = $qs === '' ? $base : $base . '?' . $qs;

        if ($page > 1) {
            return [
                'canonicalUrl' => $canonicalFirst,
                'metaRobots' => 'noindex,follow',
            ];
        }

        return [
            'canonicalUrl' => $canonicalFirst,
            'metaRobots' => null,
        ];
    }

    public static function forRaising(Request $request): array
    {
        $category = $request->query('category');
        $base = rtrim(url('/raising'), '/');
        if ($category !== null && $category !== '') {
            return [
                'canonicalUrl' => $base . '?category=' . rawurlencode((string) $category),
                'metaRobots' => null,
            ];
        }

        return [
            'canonicalUrl' => $base,
            'metaRobots' => null,
        ];
    }

    /** Blog index, etc.: page 2+ should not compete with page 1 in the index. */
    public static function forPaginatedIndex(Request $request, string $canonicalBaseUrl): array
    {
        $canonicalBaseUrl = rtrim($canonicalBaseUrl, '/');
        $page = max(1, (int) $request->query('page', 1));

        if ($page > 1) {
            return [
                'canonicalUrl' => $canonicalBaseUrl,
                'metaRobots' => 'noindex,follow',
            ];
        }

        return [
            'canonicalUrl' => $canonicalBaseUrl,
            'metaRobots' => null,
        ];
    }

    public static function forStaticPath(string $path): array
    {
        return [
            'canonicalUrl' => rtrim(url($path), '/'),
            'metaRobots' => null,
        ];
    }

    /** Login, register: keep out of the index; links on page can still be followed. */
    public static function forAuthPage(string $path): array
    {
        return [
            'canonicalUrl' => rtrim(url($path), '/'),
            'metaRobots' => 'noindex,follow',
            'includeDefaultSiteGraph' => false,
        ];
    }

    /** Saved list and other private UI. */
    public static function forPrivatePage(string $path): array
    {
        return [
            'canonicalUrl' => rtrim(url($path), '/'),
            'metaRobots' => 'noindex,nofollow',
            'includeDefaultSiteGraph' => false,
        ];
    }
}
