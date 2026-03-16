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

    private function withFunding(): \Illuminate\Database\Eloquent\Builder
    {
        return Startup::query()->with('activeFundingRound');
    }

    public function getProductOfDay(?string $category = null, int $limit = 5): Collection
    {
        $query = $this->withFunding()->active()->orderByDesc('upvotes');
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        return $query->take($limit)->get();
    }

    public function getAllStartups(?string $category = null, ?string $location = null): Collection
    {
        $query = $this->withFunding()->active()->orderByDesc('upvotes');
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        if ($location !== null && trim($location) !== '') {
            $query->where('location', 'like', '%' . trim($location) . '%');
        }
        return $query->get();
    }

    public function getAllStartupsPaginated(?string $category = null, ?string $location = null, int $perPage = 50)
    {
        $query = $this->withFunding()->active()->orderByDesc('upvotes');
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        if ($location !== null && trim($location) !== '') {
            $query->where('location', 'like', '%' . trim($location) . '%');
        }
        return $query->paginate($perPage)->withQueryString();
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

    public function getLaunchingToday(?string $category = null, bool $featuredOnly = false, int $limit = 0, ?string $location = null): Collection
    {
        $query = $this->withFunding()->active()->launchingToday()->orderByDesc('upvotes');
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        if ($featuredOnly) {
            $query->featured();
        }
        if ($location !== null && trim($location) !== '') {
            $query->where('location', 'like', '%' . trim($location) . '%');
        }
        return $limit > 0 ? $query->take($limit)->get() : $query->get();
    }

    public function getFeatured(?string $category = null, int $limit = 10, ?string $location = null): Collection
    {
        $query = $this->withFunding()->active()->featured()->orderByDesc('upvotes');
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        if ($location !== null && trim($location) !== '') {
            $query->where('location', 'like', '%' . trim($location) . '%');
        }
        return $query->take($limit)->get();
    }

    public function getFeaturedPaginated(?string $category = null, int $perPage = 50, ?string $location = null)
    {
        $query = $this->withFunding()->active()->featured()->orderByDesc('upvotes');
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        if ($location !== null && trim($location) !== '') {
            $query->where('location', 'like', '%' . trim($location) . '%');
        }
        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Top performing = most upvoted this week. Returns top $limit startups.
     */
    public function getTopPerforming(?string $category = null, bool $featuredOnly = false, int $limit = 6, ?string $location = null): Collection
    {
        $startOfWeek = now()->copy()->startOfWeek();

        $query = $this->withFunding()->active()
            ->selectRaw('startups.*, (SELECT COUNT(*) FROM startup_upvotes WHERE startup_upvotes.startup_id = startups.id AND startup_upvotes.created_at >= ?) AS upvotes_this_week', [$startOfWeek])
            ->orderByDesc('upvotes_this_week')
            ->orderByDesc('upvotes');

        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        if ($featuredOnly) {
            $query->featured();
        }
        if ($location !== null && trim($location) !== '') {
            $query->where('location', 'like', '%' . trim($location) . '%');
        }
        return $query->take($limit)->get();
    }

    public function getJustListed(?string $category = null, bool $featuredOnly = false, int $limit = 10, ?string $location = null): Collection
    {
        $query = $this->withFunding()->active()->orderByDesc('created_at');
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        if ($featuredOnly) {
            $query->featured();
        }
        if ($location !== null && trim($location) !== '') {
            $query->where('location', 'like', '%' . trim($location) . '%');
        }
        return $query->take($limit)->get();
    }

    public function getJustListedPaginated(?string $category = null, bool $featuredOnly = false, int $perPage = 50, ?string $location = null)
    {
        $query = $this->withFunding()->active()->orderByDesc('created_at');
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        if ($featuredOnly) {
            $query->featured();
        }
        if ($location !== null && trim($location) !== '') {
            $query->where('location', 'like', '%' . trim($location) . '%');
        }
        return $query->paginate($perPage)->withQueryString();
    }

    public function getRaising(?string $category = null): Collection
    {
        $query = $this->withFunding()->active()
            ->whereHas('activeFundingRound')
            ->orderByDesc('upvotes');
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        return $query->get();
    }

    public function getForSale(): Collection
    {
        return $this->withFunding()->active()
            ->forSale()
            ->orderByDesc('upvotes')
            ->get();
    }

    public function search(?string $q, ?string $category = null, ?string $location = null, int $perPage = 50)
    {
        $query = $this->withFunding()->active();
        if ($q !== null && trim($q) !== '') {
            $term = '%' . trim($q) . '%';
            $query->where(function ($builder) use ($term) {
                $builder->where('name', 'like', $term)
                    ->orWhere('tagline', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('category', 'like', $term)
                    ->orWhere('location', 'like', $term)
                    ->orWhere('founder_name', 'like', $term)
                    ->orWhere('founders', 'like', $term);
            });
        }
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        if ($location !== null && trim($location) !== '') {
            $query->where('location', 'like', '%' . trim($location) . '%');
        }
        return $query->orderByDesc('upvotes')->paginate($perPage)->withQueryString();
    }

    public function getLocationsWithCounts(): Collection
    {
        return Startup::active()
            ->selectRaw('location, count(*) as count')
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->groupBy('location')
            ->orderByDesc('count')
            ->get();
    }

    public function getLeaderboard(string $sortBy = 'upvotes', int $perPage = 20, ?string $category = null, bool $featuredOnly = false, ?string $location = null)
    {
        $query = $this->withFunding()->active();
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        if ($featuredOnly) {
            $query->featured();
        }
        if ($location !== null && trim($location) !== '') {
            $query->where('location', 'like', '%' . trim($location) . '%');
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

    public function getSimilar(Startup $startup, int $limit = 6): Collection
    {
        $query = $this->withFunding()->active()
            ->where('id', '!=', $startup->id);
        if ($startup->category !== null && $startup->category !== '') {
            $query->byCategory($startup->category);
        }
        return $query->orderByDesc('upvotes')->take($limit)->get();
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
