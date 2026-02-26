<?php

namespace App\Http\Controllers\Eden;

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
        $launchingToday = $this->startupService->getLaunchingToday();
        $featuredProducts = $this->startupService->getFeatured($categoryFilter, 10);
        $topPerforming = $this->startupService->getTopPerforming($categoryFilter, 10);
        $justListed = $this->startupService->getJustListed($categoryFilter, 10);
        if ($request->query('featured')) {
            $allStartups = $this->startupService->getFeatured($categoryFilter, 500);
        } elseif ($request->query('sort') === 'newest') {
            $allStartups = $this->startupService->getJustListed($categoryFilter, 500);
        } else {
            $allStartups = $this->startupService->getAllStartups($categoryFilter);
        }
        $categories = $this->startupService->getCategoriesWithCounts();
        $browseCategories = $this->startupService->getCategoriesWithCounts()
            ->take(12)
            ->map(fn ($c) => (object) ['name' => $c->category]);
        $leaderboardPreview = $this->startupService->getLeaderboard('upvotes', 10);

        return $this->page('home', null, 'scripts-home', [
            'launchingToday' => $launchingToday,
            'featuredProducts' => $featuredProducts,
            'topPerforming' => $topPerforming,
            'justListed' => $justListed,
            'allStartups' => $allStartups,
            'categories' => $categories,
            'browseCategories' => $browseCategories,
            'categoryFilter' => $categoryFilter,
            'leaderboardPreview' => $leaderboardPreview,
        ]);
    }

    public function leaderboard(Request $request)
    {
        $sortBy = $request->query('sort', 'upvotes');
        if (! in_array($sortBy, ['upvotes', 'views', 'clicks', 'mrr', 'revenue', 'newest'], true)) {
            $sortBy = 'upvotes';
        }
        $startups = $this->startupService->getLeaderboard($sortBy, 20);

        return $this->page('leaderboard', 'Leaderboard', null, [
            'startups' => $startups,
            'sortBy' => $sortBy,
        ]);
    }
}
