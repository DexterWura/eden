<?php

namespace App\Http\Controllers\Eden;

use App\Models\Startup;
use App\Models\Subscriber;
use App\Services\StartupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

    public function submitStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:10000',
            'category' => 'nullable|string|max:64',
            'website' => 'nullable|url|max:500',
            'location' => 'nullable|string|max:255',
            'founder_name' => 'nullable|string|max:255',
            'launch_today' => 'nullable|in:today,1,yes',
        ]);

        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $n = 0;
        while (Startup::where('slug', $slug)->exists()) {
            $n++;
            $slug = $baseSlug . '-' . $n;
        }

        $startup = new Startup();
        $startup->name = $validated['name'];
        $startup->slug = $slug;
        $startup->tagline = $validated['tagline'] ?? null;
        $startup->description = $validated['description'] ?? null;
        $startup->category = $validated['category'] ?: null;
        $startup->website = $validated['website'] ?? null;
        $startup->location = $validated['location'] ?? null;
        $startup->founder_name = $validated['founder_name'] ?? null;
        $startup->launch_date = (!empty($validated['launch_today'])) ? now() : null;
        $startup->is_featured = false;
        $startup->upvotes = 0;
        $startup->save();

        return redirect()->to(url('/startup/' . $startup->slug))->with('success', __('Your startup has been submitted.'));
    }

    public function categories()
    {
        $categories = $this->startupService->getCategoriesWithCounts();
        return $this->page('categories', 'Categories', null, ['categories' => $categories]);
    }

    public function subscribe(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email|unique:subscribers,email'], [
            'email.unique' => __('You have already subscribed.'),
        ]);
        Subscriber::create(['email' => $request->email]);
        return redirect()->back()->with('success', __('Thanks for subscribing.'));
    }
}
