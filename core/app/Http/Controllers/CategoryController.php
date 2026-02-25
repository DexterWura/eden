<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Startup;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show(Category $category, Request $request)
    {
        $startups = Startup::approved()
            ->where('category_id', $category->id)
            ->with('category')
            ->orderByDesc('approved_at')
            ->paginate(12);

        return view('categories.show', compact('category', 'startups'));
    }
}
