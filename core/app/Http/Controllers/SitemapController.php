<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Models\Frontend;
use App\Models\Listing;
use App\Models\ListingCategory;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate dynamic sitemap XML string
     */
    public function generateXml(): string
    {
        $baseUrl = url('/');
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Static pages
        $staticUrls = [
            ['url' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => route('marketplace.index'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => route('marketplace.browse'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => route('marketplace.auctions'), 'priority' => '0.8', 'changefreq' => 'daily'],
            ['url' => route('contact'), 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['url' => route('blogs'), 'priority' => '0.7', 'changefreq' => 'weekly'],
            ['url' => route('tools.index'), 'priority' => '0.7', 'changefreq' => 'monthly'],
        ];

        foreach ($staticUrls as $item) {
            $xml .= $this->urlNode($item['url'], 'daily', $item['priority']);
        }

        // Active listings
        $listings = Listing::active()
            ->select('slug', 'updated_at')
            ->orderBy('updated_at', 'desc')
            ->limit(5000)
            ->get();

        foreach ($listings as $listing) {
            $xml .= $this->urlNode(
                route('marketplace.listing.show', $listing->slug),
                'weekly',
                '0.8',
                $listing->updated_at
            );
        }

        // Categories
        $categories = ListingCategory::active()
            ->select('slug', 'updated_at')
            ->orderBy('slug')
            ->get();

        foreach ($categories as $category) {
            $xml .= $this->urlNode(
                route('marketplace.category', $category->slug),
                'weekly',
                '0.7',
                $category->updated_at
            );
        }

        // Blog posts
        $blogs = Frontend::where('data_keys', 'blog.element')
            ->select('slug', 'updated_at')
            ->orderBy('updated_at', 'desc')
            ->limit(500)
            ->get();

        foreach ($blogs as $blog) {
            $xml .= $this->urlNode(
                route('blog.details', $blog->slug),
                'monthly',
                '0.6',
                $blog->updated_at
            );
        }

        // Policy pages (from Frontend)
        $policyPages = Frontend::where('data_keys', 'policy_pages.element')
            ->select('slug', 'updated_at')
            ->get();

        foreach ($policyPages as $policy) {
            $xml .= $this->urlNode(route('policy.pages', $policy->slug), 'monthly', '0.5', $policy->updated_at);
        }

        // CMS pages (from Page model, exclude home and contact)
        $pages = Page::where('tempname', activeTemplate())
            ->whereNotIn('slug', ['/', 'contact'])
            ->select('slug', 'updated_at')
            ->get();

        foreach ($pages as $page) {
            $xml .= $this->urlNode(route('pages', $page->slug), 'monthly', '0.5', $page->updated_at);
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Generate dynamic sitemap.xml (HTTP response)
     */
    public function index(): Response
    {
        $xml = $this->generateXml();

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Charset' => 'UTF-8',
        ]);
    }

    private function urlNode(string $loc, string $changefreq, string $priority, $lastmod = null): string
    {
        $lastmod = $lastmod ? now()->parse($lastmod)->toW3cString() : now()->toW3cString();
        return "  <url>\n    <loc>" . htmlspecialchars($loc) . "</loc>\n    <lastmod>{$lastmod}</lastmod>\n    <changefreq>{$changefreq}</changefreq>\n    <priority>{$priority}</priority>\n  </url>\n";
    }

    /**
     * Generate secure default robots.txt content
     */
    public function generateRobotsTxt(): string
    {
        $sitemapUrl = url('/sitemap.xml');
        
        $content = "# robots.txt for " . gs('site_name', 'Marketplace') . "\n";
        $content .= "# Generated automatically - Secure configuration\n\n";
        
        // Allow all search engines
        $content .= "User-agent: *\n";
        
        // Allow public areas
        $content .= "Allow: /\n";
        $content .= "Allow: /marketplace/\n";
        $content .= "Allow: /blog/\n";
        $content .= "Allow: /pages/\n";
        $content .= "Allow: /tools/\n";
        $content .= "Allow: /contact\n";
        $content .= "Allow: /sitemap.xml\n";
        $content .= "Allow: /robots.txt\n";
        
        // Block sensitive/admin areas
        $content .= "\n# Block admin and private areas\n";
        $content .= "Disallow: /backoffice/\n";
        $content .= "Disallow: /user/\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /install/\n";
        $content .= "Disallow: /cron\n";
        $content .= "Disallow: /ipn/\n";
        $content .= "Disallow: /clear\n";
        
        // Block specific sensitive paths
        $content .= "\n# Block sensitive endpoints\n";
        $content .= "Disallow: /*?*\n"; // URLs with query parameters (may contain sensitive data)
        $content .= "Disallow: /marketplace/nda/\n"; // NDA documents are private
        
        // Block common admin/private patterns
        $content .= "\n# Block common private patterns\n";
        $content .= "Disallow: /*/edit\n";
        $content .= "Disallow: /*/delete\n";
        $content .= "Disallow: /*/create\n";
        $content .= "Disallow: /*/download/\n";
        
        // Crawl delay (optional, helps prevent server overload)
        $content .= "\n# Crawl delay (optional)\n";
        $content .= "Crawl-delay: 1\n";
        
        // Sitemap reference
        $content .= "\n# Sitemap location\n";
        $content .= "Sitemap: {$sitemapUrl}\n";
        
        return $content;
    }

    /**
     * Serve robots.txt - from file if exists, else default with sitemap reference
     */
    public function robots(): Response
    {
        $file = base_path('../robots.txt');
        if (file_exists($file) && filesize($file) > 0) {
            $content = file_get_contents($file);
        } else {
            // Use secure default
            $content = $this->generateRobotsTxt();
        }

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Charset' => 'UTF-8',
        ]);
    }

    /**
     * Web App Manifest for PWA support
     */
    public function manifest(): JsonResponse
    {
        $manifest = [
            'name' => gs('site_name'),
            'short_name' => gs('site_name'),
            'start_url' => url('/'),
            'display' => 'standalone',
            'theme_color' => '#0b1437',
            'background_color' => '#ffffff',
            'icons' => [
                [
                    'src' => asset('assets/images/logo_icon/logo.png'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('assets/images/logo_icon/logo.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
            ],
        ];

        return response()->json($manifest, 200, [
            'Content-Type' => 'application/manifest+json',
            'Charset' => 'UTF-8',
        ]);
    }
}
