<?php

namespace App\Services;

use App\Models\Startup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class StartupService
{
    public const PRODUCT_OF_DAY_CACHE_KEY = 'eden_product_of_day_id';
    public const PRODUCT_OF_DAY_CACHE_TTL_SECONDS = 60;

    public function getProductOfDayId(): ?int
    {
        return Cache::remember(
            self::PRODUCT_OF_DAY_CACHE_KEY,
            self::PRODUCT_OF_DAY_CACHE_TTL_SECONDS,
            fn () => Startup::active()->orderByDesc('upvotes')->value('id')
        );
    }

    public function getProductOfDay(?string $category = null, int $limit = 5): Collection
    {
        $query = Startup::active()->orderByDesc('upvotes');
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        return $query->take($limit)->get();
    }

    public function getAllStartups(?string $category = null): Collection
    {
        $query = Startup::active()->orderByDesc('upvotes');
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        return $query->get();
    }

    public function getCategoriesWithCounts(): Collection
    {
        return Startup::active()
            ->selectRaw('category, count(*) as count')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->groupBy('category')
            ->orderByDesc('count')
            ->get();
    }

    public function getLaunchingToday(?string $category = null, bool $featuredOnly = false, int $limit = 0): Collection
    {
        $query = Startup::active()->launchingToday()->orderByDesc('upvotes');
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        if ($featuredOnly) {
            $query->featured();
        }
        return $limit > 0 ? $query->take($limit)->get() : $query->get();
    }

    public function getFeatured(?string $category = null, int $limit = 10): Collection
    {
        $query = Startup::active()->featured()->orderByDesc('upvotes');
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        return $query->take($limit)->get();
    }

    /**
     * Top performing = most upvoted this week. Returns top $limit startups.
     */
    public function getTopPerforming(?string $category = null, bool $featuredOnly = false, int $limit = 6): Collection
    {
        $startOfWeek = now()->copy()->startOfWeek();

        $query = Startup::active()
            ->selectRaw('startups.*, (SELECT COUNT(*) FROM startup_upvotes WHERE startup_upvotes.startup_id = startups.id AND startup_upvotes.created_at >= ?) AS upvotes_this_week', [$startOfWeek])
            ->orderByDesc('upvotes_this_week')
            ->orderByDesc('upvotes');

        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        if ($featuredOnly) {
            $query->featured();
        }
        return $query->take($limit)->get();
    }

    public function getJustListed(?string $category = null, bool $featuredOnly = false, int $limit = 10): Collection
    {
        $query = Startup::active()->orderByDesc('created_at');
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        if ($featuredOnly) {
            $query->featured();
        }
        return $query->take($limit)->get();
    }

    public function getLeaderboard(string $sortBy = 'upvotes', int $perPage = 20, ?string $category = null, bool $featuredOnly = false)
    {
        $query = Startup::active();
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        if ($featuredOnly) {
            $query->featured();
        }
        $sortColumn = match ($sortBy) {
            'views' => 'views',
            'clicks' => 'clicks',
            'mrr' => 'mrr',
            'revenue' => 'revenue',
            'newest' => 'created_at',
            default => 'upvotes',
        };
        $query->orderByDesc($sortColumn);
        if (in_array($sortColumn, ['mrr', 'revenue'], true)) {
            $query->orderByDesc('upvotes');
        }
        return $query->paginate($perPage)->withQueryString();
    }

    public function getBySlug(string $slug): Startup
    {
        $startup = Startup::where('slug', $slug)->firstOrFail();

        if ($startup->isActive()) {
            return $startup;
        }

        if ($startup->isPending() && $startup->userCanManage(auth()->user())) {
            return $startup;
        }

        if (session('is_admin')) {
            return $startup;
        }

        abort(404);
    }
}
