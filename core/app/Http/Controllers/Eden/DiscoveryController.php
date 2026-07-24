<?php

namespace App\Http\Controllers\Eden;

use App\Models\Category;
use App\Models\Startup;
use App\Support\StartupContentPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DiscoveryController extends EdenController
{
    public function category(Request $request, string $slug)
    {
        $category = Category::query()->where('slug', $slug)->first();
        if ($category === null) {
            $legacyName = Startup::query()
                ->active()
                ->whereNotNull('category')
                ->pluck('category')
                ->unique()
                ->first(fn ($name) => Str::slug($name) === $slug);
            abort_if($legacyName === null, 404);
            $category = new Category(['name' => $legacyName, 'slug' => $slug]);
        }
        $startups = Startup::query()
            ->with('activeFundingRound')
            ->withCount('comments')
            ->active()
            ->where('category', $category->name)
            ->orderByDesc('is_featured')
            ->orderByDesc('upvotes')
            ->paginate(24)
            ->withQueryString();

        $relatedCategories = Category::query()
            ->when($category->exists, fn ($query) => $query->whereKeyNot($category->id))
            ->whereHas('startups', fn ($query) => $query->active())
            ->withCount(['startups' => fn ($query) => $query->active()])
            ->orderByDesc('startups_count')
            ->take(6)
            ->get();

        $canonicalUrl = url('/categories/' . $category->slug);
        $structuredData = $this->hubStructuredData(
            $category->name . ' startups',
            $canonicalUrl,
            collect($startups->items())
        );
        $faqs = array_values(array_filter($category->frequently_asked_questions ?? [], function ($item) {
            return trim((string) ($item['question'] ?? '')) !== ''
                && trim((string) ($item['answer'] ?? '')) !== '';
        }));
        if ($category->hasEditorialDepth() && $faqs !== []) {
            $structuredData[] = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => array_map(function ($item) {
                    return [
                        '@type' => 'Question',
                        'name' => $item['question'],
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => $item['answer'],
                        ],
                    ];
                }, $faqs),
            ];
        }

        return $this->page('discovery-hub', $category->name . ' startups', null, [
            'hubType' => 'category',
            'hubName' => $category->name,
            'hubIcon' => $category->icon,
            'introduction' => $category->introduction,
            'marketContext' => $category->market_context,
            'faqs' => $faqs,
            'startups' => $startups,
            'relatedCategories' => $relatedCategories,
        ], [
            'pageTitle' => $category->name . ' Startups | Eden',
            'metaDescription' => $this->hubMetaDescription(
                $category->introduction,
                'Discover ' . $startups->total() . ' ' . $category->name . ' startups listed on Eden.'
            ),
            'canonicalUrl' => $canonicalUrl,
            'metaRobots' => StartupContentPolicy::categoryHubIsIndexable(
                $category,
                $startups->total(),
                $request->integer('page', 1)
            ) ? null : 'noindex,follow',
            'structuredData' => $structuredData,
            'includeDefaultSiteGraph' => false,
        ]);
    }

    public function location(Request $request, string $slug)
    {
        $location = Startup::query()
            ->active()
            ->whereNotNull('location')
            ->pluck('location')
            ->unique()
            ->first(fn ($name) => Str::slug($name) === $slug);
        abort_if($location === null, 404);

        $startups = Startup::query()
            ->with('activeFundingRound')
            ->withCount('comments')
            ->active()
            ->where('location', $location)
            ->orderByDesc('is_featured')
            ->orderByDesc('upvotes')
            ->paginate(24)
            ->withQueryString();
        $categoryCounts = Startup::query()
            ->active()
            ->where('location', $location)
            ->whereNotNull('category')
            ->selectRaw('category, COUNT(*) AS total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();
        $substantiveCount = collect($startups->items())
            ->filter(fn (Startup $startup) => $startup->hasSubstantiveContent())
            ->count();
        $topCategoryNames = $categoryCounts->take(4)->pluck('category')->implode(', ');
        $introduction = $startups->total() . ' startups in ' . $location
            . ' are currently tracked on Eden'
            . ($topCategoryNames !== '' ? ', spanning ' . $topCategoryNames : '')
            . '. Browse verified product links, founder information, launches and community activity.';

        $canonicalUrl = url('/locations/' . $slug);
        $structuredData = $this->hubStructuredData(
            'Startups in ' . $location,
            $canonicalUrl,
            collect($startups->items())
        );

        return $this->page('discovery-hub', 'Startups in ' . $location, null, [
            'hubType' => 'location',
            'hubName' => $location,
            'hubIcon' => 'fa-solid fa-location-dot',
            'introduction' => $introduction,
            'marketContext' => null,
            'faqs' => [],
            'startups' => $startups,
            'categoryCounts' => $categoryCounts,
            'relatedCategories' => collect(),
        ], [
            'pageTitle' => 'Startups in ' . $location . ' | Eden',
            'metaDescription' => $this->hubMetaDescription($introduction, 'Discover startups in ' . $location . '.'),
            'canonicalUrl' => $canonicalUrl,
            'metaRobots' => StartupContentPolicy::locationHubIsIndexable(
                $startups->total(),
                $substantiveCount,
                $request->integer('page', 1)
            ) ? null : 'noindex,follow',
            'structuredData' => $structuredData,
            'includeDefaultSiteGraph' => false,
        ]);
    }

    private function hubStructuredData(string $name, string $url, $startups): array
    {
        $items = [];
        foreach ($startups as $index => $startup) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'url' => url('/startup/' . $startup->slug),
                'name' => $startup->name,
            ];
        }

        return [[
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $name,
            'url' => $url,
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => count($items),
                'itemListElement' => $items,
            ],
        ]];
    }

    private function hubMetaDescription(?string $copy, string $fallback): string
    {
        $text = preg_replace('/\s+/', ' ', strip_tags((string) $copy));
        if (trim($text) === '') {
            $text = $fallback;
        }

        return Str::limit($text, 160, '…');
    }
}
