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
        $width = 180;
        $height = 28;
        $fontSize = 12;
        $textX = $width / 2;
        $textY = $height / 2 + $fontSize / 3;

        return sprintf(
            '<?xml version="1.0" encoding="UTF-8"?><svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d"><rect width="%d" height="%d" rx="4" fill="#1a73e8"/><text x="%d" y="%d" font-family="system-ui,-apple-system,sans-serif" font-size="%d" font-weight="600" fill="#fff" text-anchor="middle" dominant-baseline="middle">%s</text></svg>',
            $width,
            $height,
            $width,
            $height,
            $width,
            $height,
            $textX,
            $textY,
            $fontSize,
            htmlspecialchars($label, ENT_XML1, 'UTF-8')
        );
    }
}
