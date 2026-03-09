<?php

namespace App\Observers;

use App\Models\BlogPost;
use App\Services\SitemapService;

class BlogPostObserver
{
    public function saved(BlogPost $post): void
    {
        $this->regenerateSitemap();
    }

    public function deleted(BlogPost $post): void
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
