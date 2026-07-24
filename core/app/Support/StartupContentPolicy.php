<?php

namespace App\Support;

use App\Models\Category;
use Illuminate\Support\Carbon;

class StartupContentPolicy
{
    public const DESCRIPTION_MIN = 250;
    public const PROBLEM_SOLVED_MIN = 80;
    public const TARGET_CUSTOMER_MIN = 40;
    public const KEY_FEATURES_MIN = 3;
    public const TRACTION_MIN = 40;
    public const FOUNDER_STORY_MIN = 80;
    public const INDEXING_SCORE_MIN = 65;
    public const LOCATION_HUB_STARTUPS_MIN = 5;
    public const LOCATION_HUB_SUBSTANTIVE_MIN = 3;
    public const INDEXING_GRACE_END = '2026-08-24 23:59:59';

    public static function indexingGracePeriodIsActive(): bool
    {
        return now()->lessThanOrEqualTo(Carbon::parse(self::INDEXING_GRACE_END));
    }

    public static function categoryHubIsIndexable(Category $category, int $startupCount, int $page): bool
    {
        return $category->hasEditorialDepth() && $startupCount > 0 && $page === 1;
    }

    public static function locationHubIsIndexable(int $startupCount, int $substantiveCount, int $page = 1): bool
    {
        return $startupCount >= self::LOCATION_HUB_STARTUPS_MIN
            && $substantiveCount >= self::LOCATION_HUB_SUBSTANTIVE_MIN
            && $page === 1;
    }
}
