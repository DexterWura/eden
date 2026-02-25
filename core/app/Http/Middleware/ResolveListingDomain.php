<?php

namespace App\Http\Middleware;

use App\Constants\Status;
use App\Models\Listing;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ResolveListingDomain
{
    private const CACHE_PREFIX = 'listing_domain:';
    private const CACHE_TTL_MINUTES = 10;

    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $normalizedHost = normalizeRequestHost($host);
        if (!$normalizedHost) {
            return $next($request);
        }

        $platformHost = platform_domain();
        if ($platformHost && $normalizedHost === $platformHost) {
            return $next($request);
        }

        $slug = Cache::remember(
            self::CACHE_PREFIX . $normalizedHost,
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            function () use ($normalizedHost) {
                $listing = Listing::where('business_type', 'domain')
                    ->where('domain_name', $normalizedHost)
                    ->whereIn('status', [Status::LISTING_ACTIVE, Status::LISTING_PENDING])
                    ->value('slug');
                return $listing;
            }
        );

        if (!$slug) {
            return $next($request);
        }

        $baseUrl = rtrim(config('app.url'), '/');
        $listingPath = '/marketplace/listing/' . $slug;
        $redirectUrl = $baseUrl . $listingPath;

        return redirect()->away($redirectUrl, Response::HTTP_FOUND);
    }
}
