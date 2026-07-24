<?php

namespace App\Http\Controllers\Eden;

use App\Models\AdSpot;
use App\Models\Startup;
use App\Services\StartupService;
use App\Support\Seo\EdenSeo;
use Illuminate\Http\Request;

class HomeController extends EdenController
{
    public function __construct(
        private StartupService $startupService
    ) {}

    public function index(Request $request)
    {
        $categoryFilter = $request->query('category');
        $locationFilter = $request->query('location');
        $featuredOnly = (bool) $request->query('featured');
        $sortNewest = $request->query('sort') === 'newest';
        $searchQuery = $request->query('q');

        if ($searchQuery !== null && trim($searchQuery) !== '') {
            $searchResults = $this->startupService->search(trim($searchQuery), $categoryFilter, $locationFilter, 50);
            $allStartups = $searchResults;
        } else {
            $searchResults = null;
            if ($featuredOnly) {
                $allStartups = $this->startupService->getFeaturedPaginated($categoryFilter, 50, $locationFilter);
            } elseif ($sortNewest) {
                $allStartups = $this->startupService->getJustListedPaginated($categoryFilter, false, 50, $locationFilter);
            } else {
                $allStartups = $this->startupService->getAllStartupsPaginated($categoryFilter, $locationFilter, 50);
            }

        }

        $categories = $this->startupService->getCategoriesWithCounts();
        $browseCategories = $this->startupService->getCategoriesWithCounts()
            ->take(12)
            ->map(fn ($c) => (object) ['name' => $c->category]);
        $featuredStartups = $this->startupService->getFeatured();
        $heroStartups = Startup::where('featured_on_hero', true)->orderBy('name')->limit(20)->get();
        $featuredFounders = collect();
        foreach ($heroStartups as $hs) {
            foreach ($hs->founders_display as $f) {
                $linkedinUrl = trim($f['linkedin_url'] ?? '');
                if ($linkedinUrl === '') {
                    continue;
                }
                $featuredFounders->push((object) [
                    'name' => $f['name'] ?? 'Founder',
                    'hero_photo_url' => $f['photo_url'] ?? null,
                    'hero_linkedin_url' => $linkedinUrl,
                ]);
            }
        }
        $featuredFounders = $featuredFounders->take(10);
        $showTrustedByBlock = $featuredFounders->isNotEmpty();
        $savedStartupIds = auth()->user()?->savedStartupIds() ?? [];

        $itemListSchema = EdenSeo::startupItemList($allStartups instanceof \Illuminate\Contracts\Pagination\Paginator ? collect($allStartups->items())->take(20) : $allStartups->take(20), 'Apps on ' . (function_exists('gs') && gs('site_name') ? gs('site_name') : 'Eden'));

        $homeSidebarAd = AdSpot::activeForPlacement('home_sidebar_1')->first();
        $homeBottomAd = AdSpot::activeForPlacement('home_bottom_banner_1')->first();

        $seo = EdenSeo::forHome($request);

        return $this->page('home', null, 'scripts-home', [
            'allStartups' => $allStartups,
            'categories' => $categories,
            'browseCategories' => $browseCategories,
            'featuredStartups' => $featuredStartups,
            'categoryFilter' => $categoryFilter,
            'featuredOnly' => $featuredOnly,
            'sortNewest' => $sortNewest,
            'searchQuery' => $searchQuery,
            'searchResults' => $searchResults,
            'productOfDayId' => $this->startupService->getProductOfDayId(),
            'showTrustedByBlock' => $showTrustedByBlock,
            'featuredFounders' => $featuredFounders,
            'savedStartupIds' => $savedStartupIds,
            'homeSidebarAd' => $homeSidebarAd,
            'homeBottomAd' => $homeBottomAd,
        ], array_merge($itemListSchema ? ['structuredData' => [$itemListSchema]] : [], $seo));
    }

    public function leaderboard(Request $request)
    {
        $sortBy = $request->query('sort', 'upvotes');
        if (! in_array($sortBy, ['upvotes', 'views', 'clicks', 'mrr', 'revenue', 'newest'], true)) {
            $sortBy = 'upvotes';
        }
        $period = $request->query('period', 'all');
        if (! in_array($period, ['all', 'week', 'month', 'year'], true)) {
            $period = 'all';
        }
        $startups = $this->startupService->getLeaderboard($sortBy, 50, period: $period);

        $itemListSchema = EdenSeo::startupItemList($startups, 'App leaderboard');
        $leaderboardAd = AdSpot::activeForPlacement('leaderboard_banner_1')->first();

        $seo = EdenSeo::forLeaderboard($request);

        return $this->page('leaderboard', 'Leaderboard', null, [
            'startups' => $startups,
            'sortBy' => $sortBy,
            'period' => $period,
            'productOfDayId' => $this->startupService->getProductOfDayId(),
            'leaderboardAd' => $leaderboardAd,
        ], array_merge($itemListSchema ? ['structuredData' => [$itemListSchema]] : [], $seo));
    }

    public function raising(Request $request)
    {
        $categoryFilter = $request->query('category');
        $startups = $this->startupService->getRaising($categoryFilter);
        $categories = $this->startupService->getCategoriesWithCounts();

        $savedStartupIds = auth()->user()?->savedStartupIds() ?? [];
        $itemListSchema = EdenSeo::startupItemList($startups, 'Apps raising funding');
        $raisingAd = AdSpot::activeForPlacement('raising_banner_1')->first();

        $seo = EdenSeo::forRaising($request);

        return $this->page('raising', 'Apps raising funding', null, [
            'startups' => $startups,
            'categories' => $categories,
            'categoryFilter' => $categoryFilter,
            'productOfDayId' => $this->startupService->getProductOfDayId(),
            'savedStartupIds' => $savedStartupIds,
            'raisingAd' => $raisingAd,
        ], array_merge($itemListSchema ? ['structuredData' => [$itemListSchema]] : [], $seo));
    }

    public function forSale(Request $request)
    {
        $startups = $this->startupService->getForSale();

        $savedStartupIds = auth()->user()?->savedStartupIds() ?? [];
        $itemListSchema = EdenSeo::startupItemList($startups, 'Apps for sale');
        $forSaleAd = AdSpot::activeForPlacement('for_sale_banner_1')->first();

        $seo = EdenSeo::forStaticPath('/for-sale');

        return $this->page('for-sale', 'Apps for sale', null, [
            'startups' => $startups,
            'productOfDayId' => $this->startupService->getProductOfDayId(),
            'savedStartupIds' => $savedStartupIds,
            'forSaleAd' => $forSaleAd,
        ], array_merge($itemListSchema ? ['structuredData' => [$itemListSchema]] : [], $seo));
    }
}
