<?php

namespace App\Support;

/**
 * First-party / sister products that always receive Pro-like listing benefits
 * (dofollow website links + elevated discovery visibility).
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

    /**
     * SQL expression (0/1) for discovery ranking: Pro owner or house website.
     */
    public static function elevatedVisibilitySql(string $websiteColumn = 'startups.website', string $userIdColumn = 'startups.user_id'): string
    {
        $websiteParts = [];
        foreach (self::DOMAINS as $domain) {
            $escaped = str_replace(['\\', '%', '_', "'"], ['\\\\', '\\%', '\\_', "''"], $domain);
            foreach ([$escaped, 'www.' . $escaped] as $host) {
                $websiteParts[] = "lower({$websiteColumn}) = '{$host}'";
                $websiteParts[] = "lower({$websiteColumn}) = 'http://{$host}'";
                $websiteParts[] = "lower({$websiteColumn}) = 'https://{$host}'";
                $websiteParts[] = "lower({$websiteColumn}) like '{$host}/%'";
                $websiteParts[] = "lower({$websiteColumn}) like '{$host}?%'";
                $websiteParts[] = "lower({$websiteColumn}) like '{$host}#%'";
                $websiteParts[] = "lower({$websiteColumn}) like 'http://{$host}/%'";
                $websiteParts[] = "lower({$websiteColumn}) like 'http://{$host}?%'";
                $websiteParts[] = "lower({$websiteColumn}) like 'http://{$host}#%'";
                $websiteParts[] = "lower({$websiteColumn}) like 'https://{$host}/%'";
                $websiteParts[] = "lower({$websiteColumn}) like 'https://{$host}?%'";
                $websiteParts[] = "lower({$websiteColumn}) like 'https://{$host}#%'";
            }
        }

        $websiteMatch = implode(' or ', $websiteParts);

        return "(case when exists (select 1 from users where users.id = {$userIdColumn} and users.is_pro = 1) then 1 when ({$websiteMatch}) then 1 else 0 end)";
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
