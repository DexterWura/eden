<?php

namespace App\Http\Controllers\Eden;

use App\Services\StartupService;
use Illuminate\Http\Request;

class HomeController extends EdenController
{
    public function __construct(
        private StartupService $startupService
    ) {}

    public function index(Request $request)
    {
        $categoryFilter = $request->query('category');
        $productOfDay = $this->startupService->getProductOfDay($categoryFilter, 5);
        $allStartups = $this->startupService->getAllStartups($categoryFilter);
        $categories = $this->startupService->getCategoriesWithCounts();

        return $this->page('home', null, 'scripts-home', [
            'productOfDay' => $productOfDay,
            'allStartups' => $allStartups,
            'categories' => $categories,
            'categoryFilter' => $categoryFilter,
        ]);
    }
}
