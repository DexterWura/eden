<?php

namespace App\Services;

use App\Models\ProductOfDayWinner;
use App\Models\Startup;
use App\Support\HouseListingBenefits;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StartupService
{
    public const PRODUCT_OF_DAY_CACHE_KEY = 'eden_product_of_day_id';

    public function __construct(
        private StartupAwardService $startupAwardService
    ) {}

    public function getProductOfDayId(): ?int
    {
        $displayDate = now()->toDateString();
        $cacheKey = self::productOfDayCacheKey($displayDate);
        $secondsUntilEndOfDay = max(60, now()->diffInSeconds(now()->copy()->endOfDay()));

        $id = Cache::remember(
            $cacheKey,
            $secondsUntilEndOfDay,
            function () {
                $yesterday = now()->subDay()->toDateString();
                $winnerId = ProductOfDayWinner::query()
                    ->where('award_date', $yesterday)
                    ->value('startup_id');

                return $winnerId !== null ? (int) $winnerId : null;
            }
        );

        return $id;
    }

    /**
     * Lock in the product of the day for a calendar date from that day's upvotes.
     *
     * @return array{startup_id: int, upvote_count: int}|null
     */
    public function selectProductOfDayForDate(CarbonInterface $awardDate): ?array
    {
        $result = $this->startupAwardService->selectProductOfDay($awardDate);
        self::clearProductOfDayCache();

        return $result;
    }

    public static function productOfDayCacheKey(string $displayDate): string
    {
        return self::PRODUCT_OF_DAY_CACHE_KEY . ':' . $displayDate;
    }

    public static function clearProductOfDayCache(): void
    {
        Cache::forget(self::productOfDayCacheKey(now()->toDateString()));
    }

    private function baseActiveQuery(): Builder
    {
        return Startup::query()
            ->with([
                'activeFundingRound',
                'user:id,is_pro',
            ])
            ->withCount('comments')
            ->active();
    }

    public function getProductOfDay(?string $category = null, int $limit = 5): Collection
    {
        $winnerId = $this->getProductOfDayId();
        if ($winnerId === null) {
            return new Collection();
        }

        $query = $this->baseActiveQuery()->where('id', $winnerId);
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }

        return $query->take($limit)->get();
    }

    public function getAllStartups(?string $category = null, ?string $location = null): Collection
    {
        $query = $this->baseActiveQuery()
            ->orderByDesc(DB::raw(HouseListingBenefits::elevatedVisibilitySql()))
            ->orderByDesc('upvotes')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
        $this->applyDiscoveryFilters($query, $category, $location);
        return $query->get();
    }

    public function getAllStartupsPaginated(?string $category = null, ?string $location = null, int $perPage = 50)
    {
        $query = $this->baseActiveQuery()
            ->orderByDesc(DB::raw(HouseListingBenefits::elevatedVisibilitySql()))
            ->orderByDesc('upvotes')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
        $this->applyDiscoveryFilters($query, $category, $location);
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
        $query = $this->baseActiveQuery()->launchingToday()->orderByDesc('upvotes');
        $this->applyDiscoveryFilters($query, $category, $location, $featuredOnly);
        return $limit > 0 ? $query->take($limit)->get() : $query->get();
    }

    public function getFeatured(?string $category = null, int $limit = 10, ?string $location = null): Collection
    {
        $query = $this->baseActiveQuery()->featured()->orderByDesc('upvotes');
        $this->applyDiscoveryFilters($query, $category, $location);
        return $query->take($limit)->get();
    }

    public function getFeaturedPaginated(?string $category = null, int $perPage = 50, ?string $location = null)
    {
        $query = $this->baseActiveQuery()->featured()->orderByDesc('upvotes');
        $this->applyDiscoveryFilters($query, $category, $location);
        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Top performing = most upvoted this week. Returns top $limit startups.
     */
    public function getTopPerforming(?string $category = null, bool $featuredOnly = false, int $limit = 6, ?string $location = null): Collection
    {
        $startOfWeek = now()->copy()->startOfWeek();

        $query = $this->baseActiveQuery()
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
        $query = $this->baseActiveQuery()->orderByDesc('created_at');
        $this->applyDiscoveryFilters($query, $category, $location, $featuredOnly);
        return $query->take($limit)->get();
    }

    public function getJustListedPaginated(?string $category = null, bool $featuredOnly = false, int $perPage = 50, ?string $location = null)
    {
        $query = $this->baseActiveQuery()->orderByDesc('created_at');
        $this->applyDiscoveryFilters($query, $category, $location, $featuredOnly);
        return $query->paginate($perPage)->withQueryString();
    }

    public function getRaising(?string $category = null): Collection
    {
        $query = $this->baseActiveQuery()
            ->whereHas('activeFundingRound')
            ->orderByDesc('upvotes');
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        return $query->get();
    }

    public function getForSale(): Collection
    {
        return $this->baseActiveQuery()
            ->forSale()
            ->orderByDesc('upvotes')
            ->get();
    }

    public function search(?string $q, ?string $category = null, ?string $location = null, int $perPage = 50)
    {
        $query = $this->baseActiveQuery();
        $this->applySearchFiltersToQuery($query, $q, $category, $location);

        return $query->orderByDesc('upvotes')->paginate($perPage)->withQueryString();
    }

    /**
     * New active listings matching the same filters as the directory search (for weekly alert emails).
     */
    public function activeStartupsMatchingFiltersSince(?string $q, ?string $category, ?string $location, mixed $since): Collection
    {
        $query = $this->baseActiveQuery()->where('created_at', '>', $since);
        $this->applySearchFiltersToQuery($query, $q, $category, $location);

        return $query->orderByDesc('created_at')->get();
    }

    private function applyDiscoveryFilters(
        Builder $query,
        ?string $category,
        ?string $location,
        bool $featuredOnly = false
    ): void {
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        if ($featuredOnly) {
            $query->featured();
        }
        if ($location !== null && trim($location) !== '') {
            $query->where('location', 'like', '%' . trim($location) . '%');
        }
    }

    private function applySearchFiltersToQuery(Builder $query, ?string $q, ?string $category, ?string $location): void
    {
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
        if ($location !== null && trim((string) $location) !== '') {
            $query->where('location', 'like', '%' . trim((string) $location) . '%');
        }
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

    public function getLeaderboard(
        string $sortBy = 'upvotes',
        int $perPage = 20,
        ?string $category = null,
        bool $featuredOnly = false,
        ?string $location = null,
        string $period = 'all'
    )
    {
        $query = $this->baseActiveQuery();
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        if ($featuredOnly) {
            $query->featured();
        }
        if ($location !== null && trim($location) !== '') {
            $query->where('location', 'like', '%' . trim($location) . '%');
        }
        $periodStart = match ($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => null,
        };
        if ($periodStart !== null) {
            $query->where('created_at', '>=', $periodStart);
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

    /**
     * Related listings: prefer same category + location, then category, then location, then popular.
     */
    public function getSimilar(Startup $startup, int $limit = 6): Collection
    {
        if ($limit < 1) {
            return new Collection();
        }

        $excludeId = $startup->id;
        $picked = new Collection();
        $excludeIds = [$excludeId];

        $pull = function (\Illuminate\Database\Eloquent\Builder $query) use (&$picked, &$excludeIds, $limit): void {
            $need = $limit - $picked->count();
            if ($need <= 0) {
                return;
            }
            $batch = $query
                ->whereNotIn('id', $excludeIds)
                ->orderByDesc('upvotes')
                ->take($need)
                ->get();
            foreach ($batch as $row) {
                $picked->push($row);
                $excludeIds[] = $row->id;
            }
        };

        $base = fn (): Builder => $this->baseActiveQuery();

        $category = $startup->category !== null && $startup->category !== '' ? $startup->category : null;
        $location = $startup->location !== null && trim((string) $startup->location) !== '' ? trim((string) $startup->location) : null;

        if ($category !== null && $location !== null) {
            $pull($base()->where('category', $category)->where('location', $location));
        }
        if ($picked->count() < $limit && $category !== null) {
            $pull($base()->byCategory($category));
        }
        if ($picked->count() < $limit && $location !== null) {
            $pull($base()->where('location', $location));
        }
        if ($picked->count() < $limit) {
            $pull($base());
        }

        return $picked->take($limit)->values();
    }

    public function getBySlug(string $slug): Startup
    {
        $startup = Startup::with('user:id,is_pro')->where('slug', $slug)->firstOrFail();

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
