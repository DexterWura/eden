<?php

namespace App\Http\Controllers\Eden;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class BadgeController extends Controller
{
    private const BADGES = [
        'listed' => 'Listed on Eden',
        'featured' => 'Featured on Eden',
        'product-of-day' => 'Product of the day on Eden',
    ];

    public function show(string $type): Response
    {
        $label = self::BADGES[$type] ?? null;
        if ($label === null) {
            abort(404);
        }

        $svg = $this->svgBadge($label);
        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function svgBadge(string $label): string
    {
        $width = 200;
        $height = 32;
        $fontSize = 11;
        $textX = $width / 2;
        $textY = $height / 2;
        $labelEscaped = htmlspecialchars($label, ENT_XML1, 'UTF-8');

        return sprintf(
            '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d">
  <defs>
    <linearGradient id="bg" x1="0%%" y1="0%%" x2="1" y2="1">
      <stop offset="0%%" stop-color="#0d9488"/>
      <stop offset="100%%" stop-color="#0f766e"/>
    </linearGradient>
    <filter id="sd" x="-20%%" y="-20%%" width="140%%" height="140%%">
      <feDropShadow dx="0" dy="1" stdDeviation="0.8" flood-opacity="0.2"/>
    </filter>
  </defs>
  <rect width="%d" height="%d" rx="8" fill="url(#bg)" filter="url(#sd)"/>
  <rect width="%d" height="%d" rx="8" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="0.5"/>
  <text x="%d" y="%d" font-family="system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,sans-serif" font-size="%d" font-weight="600" fill="#fff" text-anchor="middle" dominant-baseline="central">%s</text>
</svg>',
            $width, $height, $width, $height,
            $width, $height,
            $width, $height,
            $textX, $textY, $fontSize,
            $labelEscaped
        );
    }
}
