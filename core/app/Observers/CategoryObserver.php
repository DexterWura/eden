<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\SitemapService;

class CategoryObserver
{
    public function saved(Category $category): void
    {
        $this->regenerateSitemap();
    }

    public function deleted(Category $category): void
    {
        $this->regenerateSitemap();
    }

    private function regenerateSitemap(): void
    {
        try {
            app(SitemapService::class)->generate();
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
