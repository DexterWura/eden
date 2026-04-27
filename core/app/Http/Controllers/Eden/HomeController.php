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
            $launchingToday = collect();
            $featuredProducts = collect();
            $justListed = collect();
            $leaderboardPreview = null;
        } else {
            $searchResults = null;
            $launchingToday = $this->startupService->getLaunchingToday($categoryFilter, $featuredOnly, 0, $locationFilter);
            $featuredProducts = $this->startupService->getFeatured($categoryFilter, 10, $locationFilter);
            $justListed = $this->startupService->getJustListed($categoryFilter, $featuredOnly, 8, $locationFilter);

            if ($featuredOnly) {
                $allStartups = $this->startupService->getFeaturedPaginated($categoryFilter, 50, $locationFilter);
            } elseif ($sortNewest) {
                $allStartups = $this->startupService->getJustListedPaginated($categoryFilter, false, 50, $locationFilter);
            } else {
                $allStartups = $this->startupService->getAllStartupsPaginated($categoryFilter, $locationFilter, 50);
            }

            $leaderboardSort = $request->query('leaderboard_sort', 'upvotes');
            if (! in_array($leaderboardSort, ['upvotes', 'views', 'clicks', 'mrr', 'revenue', 'newest'], true)) {
                $leaderboardSort = 'upvotes';
            }
            $leaderboardPreview = $this->startupService->getLeaderboard($leaderboardSort, 10, $categoryFilter, $featuredOnly, $locationFilter);
        }

        $categories = $this->startupService->getCategoriesWithCounts();
        $browseCategories = $this->startupService->getCategoriesWithCounts()
            ->take(12)
            ->map(fn ($c) => (object) ['name' => $c->category]);
        $leaderboardSort = $request->query('leaderboard_sort', 'upvotes');
        if (! in_array($leaderboardSort, ['upvotes', 'views', 'clicks', 'mrr', 'revenue', 'newest'], true)) {
            $leaderboardSort = 'upvotes';
        }
        if ($searchResults === null) {
            $leaderboardPreview = $leaderboardPreview ?? $this->startupService->getLeaderboard($leaderboardSort, 10, $categoryFilter, $featuredOnly, $locationFilter);
            $hotThisWeekIds = $this->startupService
                ->getTopPerforming($categoryFilter, $featuredOnly, 6, $locationFilter)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
        } else {
            $hotThisWeekIds = [];
        }
        if ($sortNewest) {
            $hotThisWeekIds = [];
        }

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
        $savedStartupIds = auth()->check() ? auth()->user()->savedStartupsList()->select('startups.id')->pluck('id')->toArray() : [];

        $itemListSchema = $this->buildStartupItemListSchema($allStartups instanceof \Illuminate\Contracts\Pagination\Paginator ? collect($allStartups->items())->take(20) : $allStartups->take(20), 'Startups on ' . (function_exists('gs') && gs('site_name') ? gs('site_name') : 'Eden'));

        $homeAd = AdSpot::activeForPlacement('home_leaderboard_1')->first();
        $homeSidebarAd = AdSpot::activeForPlacement('home_sidebar_1')->first();
        $homeBottomAd = AdSpot::activeForPlacement('home_bottom_banner_1')->first();

        $seo = EdenSeo::forHome($request);

        return $this->page('home', null, 'scripts-home', [
            'launchingToday' => $launchingToday,
            'featuredProducts' => $featuredProducts,
            'justListed' => $justListed,
            'allStartups' => $allStartups,
            'categories' => $categories,
            'browseCategories' => $browseCategories,
            'categoryFilter' => $categoryFilter,
            'leaderboardPreview' => $leaderboardPreview,
            'leaderboardSort' => $leaderboardSort,
            'featuredOnly' => $featuredOnly,
            'sortNewest' => $sortNewest,
            'searchQuery' => $searchQuery,
            'searchResults' => $searchResults,
            'hotThisWeekIds' => $hotThisWeekIds ?? [],
            'productOfDayId' => $this->startupService->getProductOfDayId(),
            'showTrustedByBlock' => $showTrustedByBlock,
            'featuredFounders' => $featuredFounders,
            'savedStartupIds' => $savedStartupIds,
            'homeAd' => $homeAd,
            'homeSidebarAd' => $homeSidebarAd,
            'homeBottomAd' => $homeBottomAd,
        ], array_merge($itemListSchema ? ['structuredData' => [$itemListSchema]] : [], $seo));
    }

    private function buildStartupItemListSchema($startups, string $name): ?array
    {
        if ($startups->isEmpty()) {
            return null;
        }
        $items = [];
        foreach ($startups as $i => $s) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'url' => url('/startup/' . $s->slug),
                'name' => $s->name,
            ];
        }
        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $name,
            'numberOfItems' => $startups->count(),
            'itemListElement' => $items,
        ];
    }

    public function leaderboard(Request $request)
    {
        $sortBy = $request->query('sort', 'upvotes');
        if (! in_array($sortBy, ['upvotes', 'views', 'clicks', 'mrr', 'revenue', 'newest'], true)) {
            $sortBy = 'upvotes';
        }
        $startups = $this->startupService->getLeaderboard($sortBy, 50);

        $itemListSchema = $this->buildStartupItemListSchema($startups, 'Startup leaderboard');
        $leaderboardAd = AdSpot::activeForPlacement('leaderboard_banner_1')->first();

        $seo = EdenSeo::forLeaderboard($request);

        return $this->page('leaderboard', 'Leaderboard', null, [
            'startups' => $startups,
            'sortBy' => $sortBy,
            'productOfDayId' => $this->startupService->getProductOfDayId(),
            'leaderboardAd' => $leaderboardAd,
        ], array_merge($itemListSchema ? ['structuredData' => [$itemListSchema]] : [], $seo));
    }

    public function raising(Request $request)
    {
        $categoryFilter = $request->query('category');
        $startups = $this->startupService->getRaising($categoryFilter);
        $categories = $this->startupService->getCategoriesWithCounts();

        $savedStartupIds = auth()->check() ? auth()->user()->savedStartupsList()->select('startups.id')->pluck('id')->toArray() : [];
        $itemListSchema = $this->buildStartupItemListSchema($startups, 'Startups raising funding');
        $raisingAd = AdSpot::activeForPlacement('raising_banner_1')->first();

        $seo = EdenSeo::forRaising($request);

        return $this->page('raising', 'Startups raising funding', null, [
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

        $savedStartupIds = auth()->check() ? auth()->user()->savedStartupsList()->select('startups.id')->pluck('id')->toArray() : [];
        $itemListSchema = $this->buildStartupItemListSchema($startups, 'Startups for sale');
        $forSaleAd = AdSpot::activeForPlacement('for_sale_banner_1')->first();

        $seo = EdenSeo::forStaticPath('/for-sale');

        return $this->page('for-sale', 'Startups for sale', null, [
            'startups' => $startups,
            'productOfDayId' => $this->startupService->getProductOfDayId(),
            'savedStartupIds' => $savedStartupIds,
            'forSaleAd' => $forSaleAd,
        ], array_merge($itemListSchema ? ['structuredData' => [$itemListSchema]] : [], $seo));
    }
}
