<?php

namespace App\Support;

/**
 * Self-serve ad placements: segment URL slug → DB placement key, sizes, and pricing.
 */
final class AdSpotOffers
{
    /**
     * @var array<string, array{placement: string, label: string, description: string, width: int, height: int, price: float, currency: string, storage_dir: string, paypal_desc: string, paynow_title: string}>
     */
    private const BY_SEGMENT = [
        'blog' => [
            'placement' => 'blog_banner_1',
            'label' => 'Blog — top banner',
            'description' => '728×90 at the top of the blog index. High intent readers.',
            'width' => 728,
            'height' => 90,
            'price' => 2.00,
            'currency' => 'USD',
            'storage_dir' => 'blog',
            'paypal_desc' => 'Blog ad — 1 month',
            'paynow_title' => 'Blog ad — 1 month',
        ],
        'home' => [
            'placement' => 'home_leaderboard_1',
            'label' => 'Homepage — featured strip',
            'description' => '728×90 below the hero on the main directory. Maximum visibility.',
            'width' => 728,
            'height' => 90,
            'price' => 3.00,
            'currency' => 'USD',
            'storage_dir' => 'home',
            'paypal_desc' => 'Homepage ad — 1 month',
            'paynow_title' => 'Homepage ad — 1 month',
        ],
        'home-bottom' => [
            'placement' => 'home_bottom_banner_1',
            'label' => 'Homepage — above footer CTA',
            'description' => '728×90 above the “Launching something?” strip. Catches visitors after they browse listings.',
            'width' => 728,
            'height' => 90,
            'price' => 2.50,
            'currency' => 'USD',
            'storage_dir' => 'home-bottom',
            'paypal_desc' => 'Homepage footer ad — 1 month',
            'paynow_title' => 'Homepage footer ad — 1 month',
        ],
        'home-sidebar' => [
            'placement' => 'home_sidebar_1',
            'label' => 'Homepage — medium rectangle',
            'description' => '300×250 rectangle on the homepage feed. Great for product shots.',
            'width' => 300,
            'height' => 250,
            'price' => 4.00,
            'currency' => 'USD',
            'storage_dir' => 'home-sidebar',
            'paypal_desc' => 'Homepage sidebar ad — 1 month',
            'paynow_title' => 'Homepage sidebar ad — 1 month',
        ],
        'startup-sidebar' => [
            'placement' => 'startup_sidebar_1',
            'label' => 'App pages — sidebar',
            'description' => '300×250 placement beside app profiles. Reach visitors researching products and founders.',
            'width' => 300,
            'height' => 250,
            'price' => 5.00,
            'currency' => 'USD',
            'storage_dir' => 'startup-sidebar',
            'paypal_desc' => 'App profile sidebar ad — 1 month',
            'paynow_title' => 'App profile sidebar ad — 1 month',
        ],
        'leaderboard' => [
            'placement' => 'leaderboard_banner_1',
            'label' => 'Leaderboard — top banner',
            'description' => '728×90 on the full leaderboard page next to ranked startups.',
            'width' => 728,
            'height' => 90,
            'price' => 3.00,
            'currency' => 'USD',
            'storage_dir' => 'leaderboard',
            'paypal_desc' => 'Leaderboard ad — 1 month',
            'paynow_title' => 'Leaderboard ad — 1 month',
        ],
        'launching' => [
            'placement' => 'launching_today_banner_1',
            'label' => 'Launching today — banner',
            'description' => '728×90 on “Launching today” — visitors actively browsing new products.',
            'width' => 728,
            'height' => 90,
            'price' => 2.50,
            'currency' => 'USD',
            'storage_dir' => 'launching',
            'paypal_desc' => 'Launching today ad — 1 month',
            'paynow_title' => 'Launching today ad — 1 month',
        ],
        'raising' => [
            'placement' => 'raising_banner_1',
            'label' => 'Raising funding — banner',
            'description' => '728×90 on the “Raising funding” page — investors and founders raising capital.',
            'width' => 728,
            'height' => 90,
            'price' => 3.00,
            'currency' => 'USD',
            'storage_dir' => 'raising',
            'paypal_desc' => 'Raising page ad — 1 month',
            'paynow_title' => 'Raising page ad — 1 month',
        ],
        'for-sale' => [
            'placement' => 'for_sale_banner_1',
            'label' => 'Apps for sale — banner',
            'description' => '728×90 on the “For sale” page — buyers and sellers of startups.',
            'width' => 728,
            'height' => 90,
            'price' => 3.00,
            'currency' => 'USD',
            'storage_dir' => 'for-sale',
            'paypal_desc' => 'For sale page ad — 1 month',
            'paynow_title' => 'For sale page ad — 1 month',
        ],
    ];

    public static function routePattern(): string
    {
        return implode('|', array_keys(self::BY_SEGMENT));
    }

    /**
     * @return array<string, array{placement: string, label: string, description: string, width: int, height: int, price: float, currency: string, storage_dir: string, paypal_desc: string, paynow_title: string}>
     */
    public static function allBySegment(): array
    {
        return self::BY_SEGMENT;
    }

    /**
     * @return array{placement: string, label: string, description: string, width: int, height: int, price: float, currency: string, storage_dir: string, paypal_desc: string, paynow_title: string}
     */
    public static function forSegment(string $segment): array
    {
        if (! isset(self::BY_SEGMENT[$segment])) {
            abort(404);
        }

        return self::BY_SEGMENT[$segment];
    }

    public static function segmentForPlacement(string $placement): ?string
    {
        foreach (self::BY_SEGMENT as $segment => $meta) {
            if ($meta['placement'] === $placement) {
                return $segment;
            }
        }

        return null;
    }

    /**
     * URL path for the purchase form (e.g. /advertise/blog).
     */
    public static function formPath(string $segment): string
    {
        self::forSegment($segment);

        return '/advertise/' . $segment;
    }

    public static function successRedirectForPlacement(string $placement): string
    {
        $segment = self::segmentForPlacement($placement);
        if ($segment === null) {
            return '/';
        }

        return match ($segment) {
            'blog' => '/blog',
            'home', 'home-sidebar', 'home-bottom', 'startup-sidebar' => '/',
            'leaderboard' => '/leaderboard',
            'launching' => '/launching-today',
            'raising' => '/raising',
            'for-sale' => '/for-sale',
            default => '/',
        };
    }
}
