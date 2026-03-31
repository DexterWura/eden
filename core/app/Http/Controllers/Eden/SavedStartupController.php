<?php

namespace App\Http\Controllers\Eden;

use App\Models\SavedStartup;
use App\Models\Startup;
use App\Services\StartupService;
use App\Support\Seo\EdenSeo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SavedStartupController extends EdenController
{
    public function __construct(
        private StartupService $startupService
    ) {}

    public function index(Request $request): \Illuminate\Http\Response
    {
        $startups = $request->user()
            ->savedStartupsList()
            ->with('activeFundingRound')
            ->orderByPivot('created_at', 'desc')
            ->get();

        return $this->page('saved', 'My saved startups', null, [
            'startups' => $startups,
            'productOfDayId' => $this->startupService->getProductOfDayId(),
        ], EdenSeo::forPrivatePage('/saved'));
    }

    public function save(Request $request, string $slug): JsonResponse|RedirectResponse
    {
        $startup = $this->startupService->getBySlug($slug);
        $user = $request->user();
        if ($user->savedStartupsList()->where('startups.id', $startup->id)->exists()) {
            if ($request->expectsJson()) {
                return response()->json(['saved' => true, 'message' => 'Already saved.']);
            }
            return redirect()->back()->with('info', 'Already saved.');
        }
        SavedStartup::create(['user_id' => $user->id, 'startup_id' => $startup->id]);
        if ($request->expectsJson()) {
            return response()->json(['saved' => true, 'message' => 'Saved.']);
        }
        return redirect()->back()->with('success', 'Startup saved.');
    }

    public function unsave(Request $request, string $slug): JsonResponse|RedirectResponse
    {
        $startup = Startup::where('slug', $slug)->firstOrFail();
        $request->user()->savedStartupsList()->detach($startup->id);
        if ($request->expectsJson()) {
            return response()->json(['saved' => false, 'message' => 'Removed from saved.']);
        }
        return redirect()->back()->with('success', 'Removed from saved.');
    }
}
