<?php

namespace App\Http\Controllers\Eden;

use App\Services\StartupService;

class PageController extends EdenController
{
    public function __construct(
        private StartupService $startupService
    ) {}

    public function about()
    {
        return $this->page('about', 'About');
    }

    public function contact()
    {
        return $this->page('contact', 'Contact');
    }

    public function submit()
    {
        return $this->page('submit', 'Submit your startup');
    }

    public function categories()
    {
        $categories = $this->startupService->getCategoriesWithCounts();
        return $this->page('categories', 'Categories', null, ['categories' => $categories]);
    }
}
