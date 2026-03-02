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
        $featuredOnly = (bool) $request->query('featured');
        $sortNewest = $request->query('sort') === 'newest';

        $launchingToday = $this->startupService->getLaunchingToday($categoryFilter, $featuredOnly, 0);
        $featuredProducts = $this->startupService->getFeatured($categoryFilter, 10);
        $justListed = $this->startupService->getJustListed($categoryFilter, $featuredOnly, 8);

        if ($featuredOnly) {
            $allStartups = $this->startupService->getFeatured($categoryFilter, 500);
        } elseif ($sortNewest) {
            $allStartups = $this->startupService->getJustListed($categoryFilter, false, 500);
        } else {
            $allStartups = $this->startupService->getAllStartups($categoryFilter);
        }

        $categories = $this->startupService->getCategoriesWithCounts();
        $browseCategories = $this->startupService->getCategoriesWithCounts()
            ->take(12)
            ->map(fn ($c) => (object) ['name' => $c->category]);
        $leaderboardSort = $request->query('leaderboard_sort', 'upvotes');
        if (! in_array($leaderboardSort, ['upvotes', 'views', 'clicks', 'mrr', 'revenue', 'newest'], true)) {
            $leaderboardSort = 'upvotes';
        }
        $leaderboardPreview = $this->startupService->getLeaderboard($leaderboardSort, 10, $categoryFilter, $featuredOnly);

        $heroStartups = Startup::where('featured_on_hero', true)->orderBy('name')->limit(20)->get();
        $featuredFounders = collect();
        foreach ($heroStartups as $hs) {
            foreach ($hs->founders_display as $f) {
                if (! empty(trim($f['linkedin_url'] ?? ''))) {
                    $featuredFounders->push((object) [
                        'name' => $f['name'] ?? 'Founder',
                        'hero_photo_url' => $f['photo_url'] ?? null,
                        'hero_linkedin_url' => $f['linkedin_url'],
                    ]);
                }
            }
        }
        $featuredFounders = $featuredFounders->take(10);
        $showTrustedByBlock = $featuredFounders->isNotEmpty();

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
            'productOfDayId' => $this->startupService->getProductOfDayId(),
            'showTrustedByBlock' => $showTrustedByBlock,
            'featuredFounders' => $featuredFounders,
        ]);
    }

    public function leaderboard(Request $request)
    {
        $sortBy = $request->query('sort', 'upvotes');
        if (! in_array($sortBy, ['upvotes', 'views', 'clicks', 'mrr', 'revenue', 'newest'], true)) {
            $sortBy = 'upvotes';
        }
        $startups = $this->startupService->getLeaderboard($sortBy, 50);

        return $this->page('leaderboard', 'Leaderboard', null, [
            'startups' => $startups,
            'sortBy' => $sortBy,
            'productOfDayId' => $this->startupService->getProductOfDayId(),
        ]);
    }
}
