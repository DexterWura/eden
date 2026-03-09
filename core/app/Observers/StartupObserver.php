<?php

namespace App\Observers;

use App\Models\Startup;
use App\Services\SitemapService;

class StartupObserver
{
    public function saved(Startup $startup): void
    {
        $this->regenerateSitemap();
    }

    public function deleted(Startup $startup): void
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
