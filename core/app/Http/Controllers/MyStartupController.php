<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Startup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MyStartupController extends Controller
{
    public function index()
    {
        $startups = Startup::with('category')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(20);
        return view('my.startups.index', compact('startups'));
    }

    public function edit(Startup $startup)
    {
        if ($startup->user_id !== auth()->id()) {
            abort(403);
        }
        $categories = Category::orderBy('name')->get();
        $descWords = str_word_count(strip_tags($startup->description ?? ''));
        $platforms = config('social_platforms.platforms', []);
        return view('my.startups.edit', compact('startup', 'categories', 'descWords', 'platforms'));
    }

    public function update(Request $request, Startup $startup)
    {
        if ($startup->user_id !== auth()->id()) {
            abort(403);
        }
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
        ];
        foreach (array_keys(config('social_platforms.platforms', [])) as $key) {
            $rules["founder_socials.{$key}"] = 'nullable|url|max:500';
            $rules["startup_socials.{$key}"] = 'nullable|url|max:500';
        }
        $validated = $request->validate($rules);
        $validated['is_for_sale'] = $request->boolean('is_for_sale');
        $validated['last_updated_at'] = now();
        $validated['tags'] = $validated['tags'] ?? null;
        $validated['founder_socials'] = $request->input('founder_socials', []);
        $validated['startup_socials'] = $request->input('startup_socials', []);
        $startup->update(array_merge($validated, [
            'status' => $startup->status,
            'is_featured' => $startup->is_featured,
        ]));
        return redirect()->route('my.startups.index')->with('success', 'Startup updated.');
    }
}
