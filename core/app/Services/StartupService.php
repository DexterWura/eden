<?php

namespace App\Services;

use App\Models\Startup;
use Illuminate\Database\Eloquent\Collection;

class StartupService
{
    public function getProductOfDay(?string $category = null, int $limit = 5): Collection
    {
        $query = Startup::query()->orderByDesc('upvotes');
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        return $query->take($limit)->get();
    }

    public function getAllStartups(?string $category = null): Collection
    {
        $query = Startup::query()->orderByDesc('upvotes');
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        return $query->get();
    }

    public function getCategoriesWithCounts(): Collection
    {
        return Startup::query()
            ->selectRaw('category, count(*) as count')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->groupBy('category')
            ->orderByDesc('count')
            ->get();
    }

    public function getLaunchingToday(): Collection
    {
        return Startup::launchingToday()->orderByDesc('upvotes')->get();
    }

    public function getBySlug(string $slug): Startup
    {
        return Startup::where('slug', $slug)->firstOrFail();
    }
}
