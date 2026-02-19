<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Startup;
use Illuminate\Http\Request;

class StartupController extends Controller
{
    public function index(Request $request)
    {
        $query = Startup::approved()->with('category');

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('for_sale')) {
            $query->where('is_for_sale', true);
        }
        if ($request->filled('q')) {
            $q = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->q);
            $query->where(function ($qry) use ($q) {
                $qry->where('name', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%')
                    ->orWhere('tags', 'like', '%' . $q . '%');
            });
        }

        $startups = $query->orderByDesc('approved_at')->withCount('votes')->paginate(12);
        $categories = Category::orderBy('name')->get();

        return view('startups.index', compact('startups', 'categories'));
    }

    public function show(Startup $startup)
    {
        if (! $startup->approved_at) {
            abort(404);
        }
        $startup->load(['category', 'user']);
        $startup->increment('view_count');
        $startup->loadCount('votes');

        return view('startups.show', compact('startup'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('startups.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'nullable|url|max:500',
            'category_id' => 'nullable|exists:categories,id',
            'founder' => 'nullable|string|max:255',
            'tags' => 'nullable|string|max:500',
            'mrr' => 'nullable|numeric|min:0',
            'arr' => 'nullable|numeric|min:0',
            'is_for_sale' => 'boolean',
        ]);

        $slug = \Illuminate\Support\Str::slug($validated['name']);
        if ($slug === '') {
            $slug = 'startup-' . substr(md5(uniqid((string) mt_rand(), true)), 0, 8);
        }
        $base = $slug;
        $i = 0;
        while (Startup::where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$i);
        }

        Startup::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'url' => $validated['url'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'founder' => $validated['founder'] ?? null,
            'tags' => $validated['tags'] ?? null,
            'mrr' => $validated['mrr'] ?? null,
            'arr' => $validated['arr'] ?? null,
            'is_for_sale' => $request->boolean('is_for_sale'),
            'status' => Startup::STATUS_SEEDLING,
            'submitted_at' => now(),
        ]);

        return redirect()->route('startups.index')->with('success', 'Startup submitted for approval.');
    }
}
