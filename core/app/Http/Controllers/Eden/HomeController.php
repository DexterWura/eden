<?php

namespace App\Http\Controllers\Eden;

use App\Models\Startup;
use App\Models\User;
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

        $featuredFounders = User::where('featured_on_hero', true)->orderBy('name')->limit(10)->get();
        if ($featuredFounders->isNotEmpty()) {
            $this->resolveFounderPhotosAndLinkedIn($featuredFounders);
        }
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

    private function resolveFounderPhotosAndLinkedIn($founders): void
    {
        $userIds = $founders->pluck('id')->all();
        $emails = $founders->pluck('email')->filter()->unique()->all();

        $startups = Startup::query()
            ->where(function ($q) use ($userIds, $emails) {
                $q->whereIn('user_id', $userIds);
                if (! empty($emails)) {
                    $q->orWhereIn('founder_email', $emails);
                }
            })
            ->get();

        $startupsByUserId = $startups->whereNotNull('user_id')->groupBy('user_id');
        $startupsByEmail = $startups->whereNotNull('founder_email')->groupBy('founder_email');

        foreach ($founders as $founder) {
            $photoUrl = ! empty(trim((string) ($founder->hero_photo_url ?? ''))) ? $founder->hero_photo_url : null;
            $linkedinUrl = ! empty(trim((string) ($founder->linkedin_url ?? ''))) ? $founder->linkedin_url : null;

            $userStartups = collect()
                ->merge($startupsByUserId->get($founder->id, collect()))
                ->merge($startupsByEmail->get($founder->email, collect()))
                ->unique('id');

            foreach ($userStartups as $startup) {
                if ($photoUrl && $linkedinUrl) {
                    break;
                }
                $foundersList = $startup->founders_display;
                foreach ($foundersList as $f) {
                    $fEmail = $f['email'] ?? null;
                    $isMatch = ($fEmail && strcasecmp($fEmail, $founder->email) === 0)
                        || (strcasecmp($f['name'] ?? '', $founder->name) === 0);
                    if ($isMatch) {
                        if (! $photoUrl && ! empty($f['photo_url'])) {
                            $photoUrl = $f['photo_url'];
                        }
                        if (! $linkedinUrl && ! empty($f['linkedin_url'])) {
                            $linkedinUrl = $f['linkedin_url'];
                        }
                        break;
                    }
                }
                if (! $linkedinUrl && ! empty($startup->founder_linkedin_url)) {
                    $linkedinUrl = $startup->founder_linkedin_url;
                }
            }

            $founder->hero_photo_url = $photoUrl;
            $founder->hero_linkedin_url = $linkedinUrl;
        }
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
