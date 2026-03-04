<?php

namespace App\Http\Controllers\Eden;

use App\Models\Startup;
use App\Models\StartupUpvote;
use App\Services\StartupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
            'productOfDayId' => $this->startupService->getProductOfDayId(),
        ]);
    }

    public function show(string $slug)
    {
        $startup = $this->startupService->getBySlug($slug);
        $startup->increment('views');
        $hasUpvoted = auth()->check() && StartupUpvote::where('user_id', auth()->id())
            ->where('startup_id', $startup->id)->exists();

        $siteName = function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden';
        $pageTitle = $startup->name;
        if ($startup->tagline) {
            $pageTitle .= ' | ' . \Illuminate\Support\Str::limit($startup->tagline, 50);
        } elseif ($startup->category) {
            $pageTitle .= ' | ' . $startup->category;
        }
        $pageTitle .= ' | ' . $siteName;

        $metaDesc = $this->startupMetaDescription($startup);
        $metaImage = $this->startupMetaImageUrl($startup);
        $canonicalUrl = route('startup.show', $startup->slug);
        $keywords = array_filter([$startup->name, $startup->category, $startup->location, $siteName . ' startup directory']);
        $structuredData = $this->startupStructuredData($startup, $canonicalUrl, $metaImage);

        $layoutData = [
            'pageTitle' => $pageTitle,
            'metaDescription' => $metaDesc,
            'metaImage' => $metaImage,
            'canonicalUrl' => $canonicalUrl,
            'metaKeywords' => implode(', ', $keywords),
            'structuredData' => $structuredData,
        ];

        $comments = $startup->comments()->with('user:id,name')->get();

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

        return $this->page('startup-show', $pageTitle, null, [
            'startup' => $startup,
            'hasUpvoted' => $hasUpvoted,
            'isProductOfDay' => $this->startupService->getProductOfDayId() === $startup->id,
            'comments' => $comments,
            'trafficByDay' => $trafficByDay,
            'trafficTotal' => $trafficTotal,
        ], $layoutData);
    }

    private function startupMetaDescription(Startup $startup, int $maxLength = 160): string
    {
        $raw = $startup->description ?: $startup->tagline ?: $startup->name . ' – startup listed on ' . (function_exists('gs') && gs('site_name') ? gs('site_name') : 'Eden');
        $text = strip_tags(preg_replace('/\s+/', ' ', (string) $raw));
        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }
        return mb_substr($text, 0, $maxLength - 3) . '...';
    }

    private function startupMetaImageUrl(Startup $startup): ?string
    {
        if ($startup->logo_path) {
            return url()->asset($startup->logo_path);
        }
        $productImages = $startup->product_images ?? [];
        if (! empty($productImages) && is_string($productImages[0] ?? null)) {
            return url()->asset($productImages[0]);
        }
        return null;
    }

    private function startupStructuredData(Startup $startup, string $url, ?string $imageUrl): array
    {
        $siteName = function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden';
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $startup->name,
            'url' => $startup->website ?: $url,
            'description' => $this->startupMetaDescription($startup, 500),
        ];
        if ($imageUrl) {
            $data['image'] = $imageUrl;
        }
        if ($startup->location) {
            $data['address'] = ['@type' => 'PostalAddress', 'addressLocality' => $startup->location];
        }
        $founders = $startup->founders_display ?? [];
        if (! empty($founders)) {
            $data['member'] = array_values(array_map(function ($f) {
                $member = ['@type' => 'Person', 'name' => $f['name'] ?? ''];
                if (! empty($f['email'])) {
                    $member['email'] = $f['email'];
                }
                return $member;
            }, $founders));
        }
        $data['publisher'] = [
            '@type' => 'Organization',
            'name' => $siteName,
            'url' => url('/'),
        ];
        return $data;
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

    public function upvote(Request $request, string $slug): RedirectResponse|JsonResponse
    {
        $startup = Startup::where('slug', $slug)->first();
        if (!$startup) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'not_found',
                    'message' => 'Startup not found.',
                ], 404);
            }
            return redirect()->back()->with('error', 'Startup not found.');
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
                    'message' => 'This startup is not available.',
                ], 403);
            }
            return redirect()->back()->with('error', 'This startup is not available.');
        }
        $exists = StartupUpvote::where('user_id', auth()->id())->where('startup_id', $startup->id)->exists();
        if ($exists) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'ok',
                    'message' => 'You already upvoted this startup.',
                    'already' => true,
                    'upvotes' => (int) $startup->upvotes,
                ], 200);
            }
            return redirect()->back()->with('info', 'You already upvoted this startup.');
        }
        StartupUpvote::create(['user_id' => auth()->id(), 'startup_id' => $startup->id]);
        $startup->increment('upvotes');
        Cache::forget(StartupService::PRODUCT_OF_DAY_CACHE_KEY);
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
