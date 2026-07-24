<?php

namespace App\Http\Controllers\Eden;

use App\Http\Controllers\Controller;
use App\Services\RobotsTxtService;
use App\Services\SitemapService;
use Illuminate\Http\Response;

class TechnicalSeoController extends Controller
{
    public function sitemap(SitemapService $sitemapService): Response
    {
        return response($sitemapService->render(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function robots(): Response
    {
        $configured = function_exists('gs') ? trim((string) (gs('robots_txt') ?? '')) : '';
        $content = $configured !== '' ? $configured : RobotsTxtService::recommendedContent();

        return response(trim($content) . "\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function ads(): Response
    {
        $script = function_exists('gs') ? (string) (gs('adsense_script') ?? '') : '';
        preg_match('/ca-pub-(\d{10,})/', $script, $matches);
        if (empty($matches[1])) {
            abort(404);
        }

        $content = 'google.com, pub-' . $matches[1] . ', DIRECT, f08c47fec0942fa0';

        return response($content . "\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
