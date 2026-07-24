<?php

namespace App\Support;

/**
 * First-party / sister products that keep a dofollow website backlink
 * even when the founder account is not Pro.
 */
final class HouseListingBenefits
{
    /**
     * @var list<string>
     */
    private const DOMAINS = [
        'socialplod.com',
        'dextersoft.com',
        'flipit.co.zw',
    ];

    /**
     * @return list<string>
     */
    public static function domains(): array
    {
        return self::DOMAINS;
    }

    public static function matchesWebsite(?string $website): bool
    {
        $host = self::hostFromWebsite($website);
        if ($host === null) {
            return false;
        }

        foreach (self::DOMAINS as $domain) {
            if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                return true;
            }
        }

        return false;
    }

    private static function hostFromWebsite(?string $website): ?string
    {
        $raw = trim((string) $website);
        if ($raw === '') {
            return null;
        }

        if (! str_contains($raw, '://')) {
            $raw = 'https://' . $raw;
        }

        $host = parse_url($raw, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return null;
        }

        return strtolower(ltrim($host, '.'));
    }
}
