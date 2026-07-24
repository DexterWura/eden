<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Startup;
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

    /**
     * Shared profile-quality checks used by indexing, forms, and founder guidance.
     *
     * @return array<string, array{label: string, complete: bool}>
     */
    public static function profileChecks(Startup $startup): array
    {
        return [
            'description' => [
                'label' => 'Add a detailed product description',
                'complete' => mb_strlen(trim((string) $startup->description)) >= self::DESCRIPTION_MIN,
            ],
            'problem_solved' => [
                'label' => 'Explain the problem you solve',
                'complete' => mb_strlen(trim((string) $startup->problem_solved)) >= self::PROBLEM_SOLVED_MIN,
            ],
            'target_customer' => [
                'label' => 'Describe your target customer',
                'complete' => mb_strlen(trim((string) $startup->target_customer)) >= self::TARGET_CUSTOMER_MIN,
            ],
            'key_features' => [
                'label' => 'List at least three key features',
                'complete' => count(array_filter($startup->key_features ?? [])) >= self::KEY_FEATURES_MIN,
            ],
            'pricing_model' => [
                'label' => 'Add your pricing model',
                'complete' => trim((string) $startup->pricing_model) !== '',
            ],
            'markets_served' => [
                'label' => 'Add the markets you serve',
                'complete' => trim((string) $startup->markets_served) !== '',
            ],
            'traction' => [
                'label' => 'Share meaningful traction',
                'complete' => mb_strlen(trim((string) $startup->traction)) >= self::TRACTION_MIN,
            ],
            'founder_story' => [
                'label' => 'Tell your founder story',
                'complete' => mb_strlen(trim((string) $startup->founder_story)) >= self::FOUNDER_STORY_MIN,
            ],
            'category' => [
                'label' => 'Choose a category',
                'complete' => trim((string) $startup->category) !== '',
            ],
            'website' => [
                'label' => 'Add your website',
                'complete' => trim((string) $startup->website) !== '',
            ],
            'media' => [
                'label' => 'Upload a logo or product image',
                'complete' => trim((string) $startup->logo_path) !== '' || count($startup->product_images ?? []) > 0,
            ],
        ];
    }

    /** @return array<int, string> */
    public static function profileGaps(Startup $startup): array
    {
        return collect(self::profileChecks($startup))
            ->reject(fn (array $check) => $check['complete'])
            ->pluck('label')
            ->values()
            ->all();
    }

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
