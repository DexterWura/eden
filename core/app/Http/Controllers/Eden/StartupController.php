<?php

namespace App\Http\Controllers\Eden;

use App\Models\AdSpot;
use App\Models\Startup;
use App\Models\StartupReport;
use App\Models\StartupUpvote;
use App\Services\StartupSharePreviewService;
use App\Services\StartupService;
use App\Support\Seo\EdenSeo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StartupController extends EdenController
{
    public function __construct(
        private StartupService $startupService,
        private StartupSharePreviewService $sharePreviewService
    ) {}

    public function launchingToday()
    {
        $startups = $this->startupService->getLaunchingToday();
        $savedStartupIds = auth()->user()?->savedStartupIds() ?? [];
        $itemListSchema = EdenSeo::startupItemList($startups, 'Apps launching today');
        $launchingAd = AdSpot::activeForPlacement('launching_today_banner_1')->first();

        return $this->page('launching-today', 'Launching today', 'scripts-launching-today', [
            'startups' => $startups,
            'productOfDayId' => $this->startupService->getProductOfDayId(),
            'savedStartupIds' => $savedStartupIds,
            'launchingAd' => $launchingAd,
        ], array_merge($itemListSchema ? ['structuredData' => [$itemListSchema]] : [], EdenSeo::forStaticPath('/launching-today')));
    }

    public function show(string $slug)
    {
        $startup = $this->startupService->getBySlug($slug);
        $startup->increment('views');
        $hasUpvoted = auth()->check() && StartupUpvote::where('user_id', auth()->id())
            ->where('startup_id', $startup->id)->exists();
        $hasSaved = auth()->check() && auth()->user()->savedStartupsList()->where('startups.id', $startup->id)->exists();

        $layoutData = $this->sharePreviewService->build($startup);
        $structuredData = $layoutData['structuredData'];
        $structuredData[] = $this->breadcrumbSchema([
            ['name' => 'Home', 'url' => url('/')],
            ['name' => $startup->name, 'url' => $layoutData['canonicalUrl']],
        ]);

        $similarStartups = $this->startupService->getSimilar($startup, 6);
        $similarSchema = EdenSeo::startupItemList($similarStartups, 'Similar apps');
        if ($similarSchema !== null) {
            $structuredData[] = $similarSchema;
        }

        $layoutData['structuredData'] = $structuredData;

        $comments = $startup->comments()->with(['user:id,name', 'founderResponder:id,name'])->get();

        $trafficByDay = [];
        $trafficTotal = 0;
        if ($startup->traffic_tracking_enabled) {
            $trafficRows = $startup->trafficDaily()
                ->where('date', '>=', now()->subDays(14))
                ->orderBy('date')
                ->get();
            foreach ($trafficRows as $row) {
                $trafficByDay[$row->date->format('Y-m-d')] = $row->visits;
                $trafficTotal += $row->visits;
            }
        }

        $savedStartupIds = auth()->user()?->savedStartupIds() ?? [];

        $isProductOfDay = $this->startupService->getProductOfDayId() === $startup->id;
        $productOfDayDate = $startup->product_of_day_at;
        $isProductOfDayToday = $isProductOfDay;
        $startupSidebarAd = AdSpot::activeForPlacement('startup_sidebar_1')->first();

        return $this->page('startup-show', $layoutData['pageTitle'], null, [
            'startup' => $startup,
            'hasUpvoted' => $hasUpvoted,
            'hasSaved' => $hasSaved,
            'isProductOfDay' => $isProductOfDay,
            'productOfDayDate' => $productOfDayDate,
            'isProductOfDayToday' => $isProductOfDayToday,
            'comments' => $comments,
            'trafficByDay' => $trafficByDay,
            'trafficTotal' => $trafficTotal,
            'similarStartups' => $similarStartups,
            'savedStartupIds' => $savedStartupIds,
            'startupSidebarAd' => $startupSidebarAd,
            'reportReasons' => StartupReport::reasonLabels(),
            'sharePreview' => $layoutData,
        ], $layoutData);
    }

    private function breadcrumbSchema(array $items): array
    {
        $listItems = [];
        foreach ($items as $i => $item) {
            $listItems[] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ];
        }
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $listItems,
        ];
    }

    public function out(string $slug): RedirectResponse
    {
        $startup = Startup::where('slug', $slug)->first();
        if (! $startup || ! $startup->isActive() || empty($startup->website)) {
            return redirect()->to(url('/startup/' . $slug))->with('error', 'App or website not found.');
        }
        $startup->increment('clicks');
        $website = $startup->website;
        $separator = str_contains($website, '?') ? '&' : '?';
        return redirect()->away($website . $separator . 'ref=eden');
    }

    public function upvote(Request $request, string $slug): RedirectResponse|JsonResponse
    {
        $startup = Startup::where('slug', $slug)->first();
        if (!$startup) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'not_found',
                    'message' => 'App not found.',
                ], 404);
            }
            return redirect()->back()->with('error', 'App not found.');
        }
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'unauthenticated',
                    'message' => 'Log in to upvote.',
                    'login_url' => route('login'),
                ], 401);
            }
            return redirect()->route('login')->with('info', 'Please log in to upvote.');
        }
        if (!$startup->isActive()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'forbidden',
                    'message' => 'This app is not available.',
                ], 403);
            }
            return redirect()->back()->with('error', 'This app is not available.');
        }
        $exists = StartupUpvote::where('user_id', auth()->id())->where('startup_id', $startup->id)->exists();
        if ($exists) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'ok',
                    'message' => 'You already upvoted this app.',
                    'already' => true,
                    'upvotes' => (int) $startup->upvotes,
                ], 200);
            }
            return redirect()->back()->with('info', 'You already upvoted this app.');
        }
        StartupUpvote::create(['user_id' => auth()->id(), 'startup_id' => $startup->id]);
        $startup->increment('upvotes');
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Upvote recorded.',
                'already' => false,
                'upvotes' => (int) $startup->fresh()->upvotes,
            ], 201);
        }
        return redirect()->back()->with('success', 'Upvote recorded. Thanks!');
    }
}
