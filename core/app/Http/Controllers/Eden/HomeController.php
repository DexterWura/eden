<?php

namespace App\Http\Controllers\Eden;

use App\Models\Startup;
use App\Services\StartupService;
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
            $searchResults = $this->startupService->search(trim($searchQuery), $categoryFilter, $locationFilter, 100);
            $allStartups = $searchResults->getCollection();
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
                $allStartups = $this->startupService->getFeatured($categoryFilter, 500, $locationFilter);
            } elseif ($sortNewest) {
                $allStartups = $this->startupService->getJustListed($categoryFilter, false, 500, $locationFilter);
            } else {
                $allStartups = $this->startupService->getAllStartups($categoryFilter, $locationFilter);
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
        $browseLocations = $this->startupService->getLocationsWithCounts()
            ->take(12)
            ->map(fn ($l) => (object) ['name' => $l->location]);
        $leaderboardSort = $request->query('leaderboard_sort', 'upvotes');
        if (! in_array($leaderboardSort, ['upvotes', 'views', 'clicks', 'mrr', 'revenue', 'newest'], true)) {
            $leaderboardSort = 'upvotes';
        }
        if ($searchResults === null) {
            $leaderboardPreview = $leaderboardPreview ?? $this->startupService->getLeaderboard($leaderboardSort, 10, $categoryFilter, $featuredOnly, $locationFilter);
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
        $savedStartupIds = auth()->check() ? auth()->user()->savedStartupsList()->pluck('id')->toArray() : [];

        return $this->page('home', null, 'scripts-home', [
            'launchingToday' => $launchingToday,
            'featuredProducts' => $featuredProducts,
            'justListed' => $justListed,
            'allStartups' => $allStartups,
            'categories' => $categories,
            'browseCategories' => $browseCategories,
            'browseLocations' => $browseLocations ?? collect(),
            'categoryFilter' => $categoryFilter,
            'locationFilter' => $locationFilter,
            'leaderboardPreview' => $leaderboardPreview,
            'leaderboardSort' => $leaderboardSort,
            'featuredOnly' => $featuredOnly,
            'sortNewest' => $sortNewest,
            'searchQuery' => $searchQuery,
            'searchResults' => $searchResults,
            'productOfDayId' => $this->startupService->getProductOfDayId(),
            'showTrustedByBlock' => $showTrustedByBlock,
            'featuredFounders' => $featuredFounders,
            'savedStartupIds' => $savedStartupIds,
        ]);
    }

    public function leaderboard(Request $request)
    {
        $sortBy = $request->query('sort', 'upvotes');
        if (! in_array($sortBy, ['upvotes', 'views', 'clicks', 'mrr', 'revenue', 'newest'], true)) {
            $sortBy = 'upvotes';
        }
        $locationFilter = $request->query('location');
        $startups = $this->startupService->getLeaderboard($sortBy, 50, null, false, $locationFilter);
        $browseLocations = $this->startupService->getLocationsWithCounts();

        return $this->page('leaderboard', 'Leaderboard', null, [
            'startups' => $startups,
            'sortBy' => $sortBy,
            'locationFilter' => $locationFilter,
            'browseLocations' => $browseLocations,
            'productOfDayId' => $this->startupService->getProductOfDayId(),
        ]);
    }

    public function raising(Request $request)
    {
        $categoryFilter = $request->query('category');
        $startups = $this->startupService->getRaising($categoryFilter);
        $categories = $this->startupService->getCategoriesWithCounts();

        $savedStartupIds = auth()->check() ? auth()->user()->savedStartupsList()->pluck('id')->toArray() : [];
        return $this->page('raising', 'Startups raising funding', null, [
            'startups' => $startups,
            'categories' => $categories,
            'categoryFilter' => $categoryFilter,
            'productOfDayId' => $this->startupService->getProductOfDayId(),
            'savedStartupIds' => $savedStartupIds,
        ]);
    }

    public function forSale(Request $request)
    {
        $startups = $this->startupService->getForSale();

        $savedStartupIds = auth()->check() ? auth()->user()->savedStartupsList()->pluck('id')->toArray() : [];
        return $this->page('for-sale', 'Startups for sale', null, [
            'startups' => $startups,
            'productOfDayId' => $this->startupService->getProductOfDayId(),
            'savedStartupIds' => $savedStartupIds,
        ]);
    }
}
