<?php

namespace App\Http\Controllers\Eden;

use App\Models\Startup;
use App\Models\StartupUpvote;
use App\Services\StartupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StartupController extends EdenController
{
    public function __construct(
        private StartupService $startupService
    ) {}

    public function launchingToday()
    {
        $startups = $this->startupService->getLaunchingToday();
        return $this->page('launching-today', 'Launching today', 'scripts-launching-today', [
            'startups' => $startups,
        ]);
    }

    public function show(string $slug)
    {
        $startup = $this->startupService->getBySlug($slug);
        $startup->increment('views');
        $hasUpvoted = auth()->check() && StartupUpvote::where('user_id', auth()->id())
            ->where('startup_id', $startup->id)->exists();
        return $this->page('startup-show', $startup->name, null, [
            'startup' => $startup,
            'hasUpvoted' => $hasUpvoted,
        ]);
    }

    public function out(string $slug): RedirectResponse
    {
        $startup = Startup::where('slug', $slug)->first();
        if (! $startup || ! $startup->isActive() || empty($startup->website)) {
            return redirect()->to(url('/startup/' . $slug))->with('error', 'Startup or website not found.');
        }
        $startup->increment('clicks');
        return redirect()->away($startup->website);
    }

    public function upvote(Request $request, string $slug): RedirectResponse
    {
        $startup = Startup::where('slug', $slug)->first();
        if (!$startup) {
            return redirect()->back()->with('error', 'Startup not found.');
        }
        if (!auth()->check()) {
            return redirect()->route('login')->with('info', 'Please log in to upvote.');
        }
        if (!$startup->isActive()) {
            return redirect()->back()->with('error', 'This startup is not available.');
        }
        $exists = StartupUpvote::where('user_id', auth()->id())->where('startup_id', $startup->id)->exists();
        if ($exists) {
            return redirect()->back()->with('info', 'You already upvoted this startup.');
        }
        StartupUpvote::create(['user_id' => auth()->id(), 'startup_id' => $startup->id]);
        $startup->increment('upvotes');
        return redirect()->back()->with('success', 'Upvote recorded. Thanks!');
    }
}
