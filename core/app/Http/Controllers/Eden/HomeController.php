<?php

namespace App\Http\Controllers\Eden;

use App\Models\Category;
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
        $browseCategories = Category::orderBy('sort_order')->get();
        $leaderboardPreview = $this->startupService->getLeaderboard($categoryFilter, 'upvotes', 10);

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
        $categoryFilter = $request->query('category');
        $sortBy = $request->query('sort', 'upvotes');
        if (!in_array($sortBy, ['upvotes', 'newest'], true)) {
            $sortBy = 'upvotes';
        }
        $startups = $this->startupService->getLeaderboard($categoryFilter, $sortBy, 20);
        $browseCategories = Category::orderBy('sort_order')->get();

        return $this->page('leaderboard', 'Leaderboard', null, [
            'startups' => $startups,
            'browseCategories' => $browseCategories,
            'categoryFilter' => $categoryFilter,
            'sortBy' => $sortBy,
        ]);
    }
}
