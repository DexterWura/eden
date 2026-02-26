<?php

namespace App\Services;

use App\Models\Startup;
use Illuminate\Database\Eloquent\Collection;

class StartupService
{
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

    public function getLaunchingToday(): Collection
    {
        return Startup::active()->launchingToday()->orderByDesc('upvotes')->get();
    }

    public function getFeatured(?string $category = null, int $limit = 10): Collection
    {
        $query = Startup::active()->featured()->orderByDesc('upvotes');
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        return $query->take($limit)->get();
    }

    public function getTopPerforming(?string $category = null, int $limit = 10): Collection
    {
        $query = Startup::active()->orderByDesc('upvotes');
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        return $query->take($limit)->get();
    }

    public function getJustListed(?string $category = null, int $limit = 10): Collection
    {
        $query = Startup::active()->orderByDesc('created_at');
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        return $query->take($limit)->get();
    }

    public function getLeaderboard(?string $category = null, string $sortBy = 'upvotes', int $perPage = 20)
    {
        $query = Startup::active();
        if ($category !== null && $category !== '') {
            $query->byCategory($category);
        }
        if ($sortBy === 'newest') {
            $query->orderByDesc('created_at');
        } else {
            $query->orderByDesc('upvotes');
        }
        return $query->paginate($perPage)->withQueryString();
    }

    public function getBySlug(string $slug): Startup
    {
        return Startup::active()->where('slug', $slug)->firstOrFail();
    }
}
