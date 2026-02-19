<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Startup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StartupController extends Controller
{
    public function index(Request $request)
    {
        $query = Startup::with('category', 'user');
        if ($request->filled('q')) {
            $q = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->q);
            $query->where(function ($qry) use ($q) {
                $qry->where('name', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%')
                    ->orWhere('tags', 'like', '%' . $q . '%');
            });
        }
        $startups = $query->latest()->paginate(20);
        return view('admin.startups.index', compact('startups'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $platforms = config('social_platforms.platforms', []);
        return view('admin.startups.create', compact('categories', 'platforms'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'url' => 'nullable|url|max:500',
            'category_id' => 'nullable|exists:categories,id',
            'founder' => 'nullable|string|max:255',
            'tags' => 'nullable|string|max:500',
            'mrr' => 'nullable|numeric|min:0',
            'arr' => 'nullable|numeric|min:0',
            'is_for_sale' => 'boolean',
            'status' => 'in:seedling,sapling,flourishing,wilted',
            'is_featured' => 'boolean',
        ];
        foreach (array_keys(config('social_platforms.platforms', [])) as $key) {
            $rules["founder_socials.{$key}"] = 'nullable|url|max:500';
            $rules["startup_socials.{$key}"] = 'nullable|url|max:500';
        }
        $validated = $request->validate($rules);
        $slug = $validated['slug'] ?: Str::slug($validated['name']);
        if ($slug === '') {
            $slug = 'startup-' . substr(md5(uniqid((string) mt_rand(), true)), 0, 8);
        }
        $base = $slug;
        $i = 0;
        while (Startup::where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$i);
        }
        $validated['slug'] = $slug;
        $validated['user_id'] = auth()->id();
        $validated['is_for_sale'] = $request->boolean('is_for_sale');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['approved_at'] = now();
        $validated['submitted_at'] = now();
        $validated['last_updated_at'] = now();
        $validated['status'] = $request->input('status', Startup::STATUS_SEEDLING);
        $validated['founder_socials'] = $request->input('founder_socials', []);
        $validated['startup_socials'] = $request->input('startup_socials', []);
        Startup::create($validated);
        return redirect()->route('admin.startups.index')->with('success', 'Startup created and approved.');
    }

    public function edit(Startup $startup)
    {
        $categories = Category::orderBy('name')->get();
        $platforms = config('social_platforms.platforms', []);
        return view('admin.startups.edit', compact('startup', 'categories', 'platforms'));
    }

    public function update(Request $request, Startup $startup)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:startups,slug,' . $startup->id,
            'description' => 'nullable|string',
            'url' => 'nullable|url|max:500',
            'category_id' => 'nullable|exists:categories,id',
            'founder' => 'nullable|string|max:255',
            'tags' => 'nullable|string|max:500',
            'mrr' => 'nullable|numeric|min:0',
            'arr' => 'nullable|numeric|min:0',
            'is_for_sale' => 'boolean',
            'status' => 'in:seedling,sapling,flourishing,wilted',
            'is_featured' => 'boolean',
        ];
        foreach (array_keys(config('social_platforms.platforms', [])) as $key) {
            $rules["founder_socials.{$key}"] = 'nullable|url|max:500';
            $rules["startup_socials.{$key}"] = 'nullable|url|max:500';
        }
        $validated = $request->validate($rules);
        $validated['is_for_sale'] = $request->boolean('is_for_sale');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['last_updated_at'] = now();
        $validated['tags'] = $validated['tags'] ?? null;
        $validated['founder_socials'] = $request->input('founder_socials', []);
        $validated['startup_socials'] = $request->input('startup_socials', []);
        $startup->update($validated);
        return redirect()->route('admin.startups.index')->with('success', 'Startup updated.');
    }

    public function destroy(Startup $startup)
    {
        $startup->delete();
        return redirect()->route('admin.startups.index')->with('success', 'Startup deleted.');
    }

    public function approve(Startup $startup)
    {
        $startup->update(['approved_at' => now(), 'last_updated_at' => now()]);
        return back()->with('success', 'Startup approved.');
    }

    public function reject(Startup $startup)
    {
        $startup->update(['approved_at' => null]);
        return back()->with('success', 'Startup rejected.');
    }
}
