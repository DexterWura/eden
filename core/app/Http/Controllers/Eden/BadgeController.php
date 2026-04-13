<?php

namespace App\Http\Controllers\Eden;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BadgeController extends Controller
{
    private const BADGE_KICKERS = [
        'listed' => 'LISTED ON',
        'featured' => 'FEATURED ON',
        'product-of-day' => 'PRODUCT OF THE DAY',
    ];

    public function show(Request $request, string $type): Response
    {
        $kicker = self::BADGE_KICKERS[$type] ?? null;
        if ($kicker === null) {
            abort(404);
        }

        $theme = $request->query('theme', 'dark');
        if (!in_array($theme, ['dark', 'light'], true)) {
            $theme = 'dark';
        }

        $svg = $this->svgBadge($kicker, $this->siteName(), $theme);
        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function siteName(): string
    {
        return function_exists('gs') && gs('site_name')
            ? (string) gs('site_name')
            : 'Eden';
    }

    private function svgBadge(string $kicker, string $title, string $theme): string
    {
        $width = 220;
        $height = 52;
        $isLight = $theme === 'light';
        $kickerEscaped = htmlspecialchars($kicker, ENT_XML1, 'UTF-8');
        $titleEscaped = htmlspecialchars($title, ENT_XML1, 'UTF-8');
        $bgStart = $isLight ? '#ffffff' : '#14181f';
        $bgEnd = $isLight ? '#f7f8fa' : '#1f2631';
        $stroke = $isLight ? 'rgba(15,23,42,0.16)' : 'rgba(255,255,255,0.14)';
        $kickerColor = $isLight ? '#64748b' : '#d1d5db';
        $titleColor = $isLight ? '#0f172a' : '#f8fafc';
        $shadowOpacity = $isLight ? '0.08' : '0.26';

        return sprintf(
            '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d">
  <defs>
    <linearGradient id="bg" x1="0%%" y1="0%%" x2="100%%" y2="100%%">
      <stop offset="0%%" stop-color="%s"/>
      <stop offset="100%%" stop-color="%s"/>
    </linearGradient>
    <linearGradient id="iconBg" x1="0%%" y1="0%%" x2="100%%" y2="100%%">
      <stop offset="0%%" stop-color="#fb7185"/>
      <stop offset="100%%" stop-color="#f43f5e"/>
    </linearGradient>
    <filter id="sd" x="-20%%" y="-20%%" width="140%%" height="140%%">
      <feDropShadow dx="0" dy="2" stdDeviation="2" flood-opacity="%s"/>
    </filter>
  </defs>
  <rect width="%d" height="%d" rx="26" fill="url(#bg)" filter="url(#sd)"/>
  <rect x="0.5" y="0.5" width="%d" height="%d" rx="25.5" fill="none" stroke="%s" stroke-width="1"/>
  <circle cx="28" cy="26" r="15" fill="url(#iconBg)"/>
  <path d="M28 16.6c-3.7 4.5-5.6 7.1-5.6 10.1A5.6 5.6 0 0 0 28 32.3a5.6 5.6 0 0 0 5.6-5.6c0-3-1.9-5.6-5.6-10.1z" fill="#fff"/>
  <text x="52" y="21" font-family="system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,sans-serif" font-size="8.6" font-weight="700" fill="%s" letter-spacing="0.9">%s</text>
  <text x="52" y="35" font-family="system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,sans-serif" font-size="17" font-weight="700" fill="%s">%s</text>
</svg>',
            $width, $height, $width, $height,
            $bgStart, $bgEnd,
            $shadowOpacity,
            $width, $height,
            $width - 1, $height - 1, $stroke,
            $kickerColor, $kickerEscaped,
            $titleColor, $titleEscaped
        );
    }
}
