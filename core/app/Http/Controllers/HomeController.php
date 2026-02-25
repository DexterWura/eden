<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Startup;

class HomeController extends Controller
{
    public function __invoke()
    {
        $featured = Startup::approved()
            ->where('is_featured', true)
            ->orderByDesc('approved_at')
            ->limit(6)
            ->get();

        $categories = Category::orderBy('name')->get();

        return view('home', compact('featured', 'categories'));
    }
}
