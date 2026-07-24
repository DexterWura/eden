<?php

namespace App\Http\Controllers\Eden;

use App\Models\BlogPost;
use App\Models\AdSpot;
use App\Models\Category;
use App\Models\Startup;
use App\Support\Seo\EdenSeo;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends EdenController
{
    public function index(Request $request): Response
    {
        $seo = EdenSeo::forPaginatedIndex($request, url('/blog'));

        $posts = BlogPost::published()
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString();

        $blogAd = AdSpot::activeForPlacement('blog_banner_1')->first();

        $siteName = function_exists('gs') && gs('site_name') ? gs('site_name') : 'Eden';
        $pageTitle = 'Blog | ' . $siteName;
        $metaDescription = function_exists('gs') && gs('meta_description') ? gs('meta_description') : 'Articles and updates from ' . $siteName . '.';

        return response()->view('eden.layout', array_merge([
            'title' => 'Blog',
            'pageTitle' => $pageTitle,
            'metaDescription' => $metaDescription,
            'content' => view('eden.blog.list', [
                'posts' => $posts,
                'blogAd' => $blogAd,
            ])->render(),
            'scripts' => '',
        ], $seo));
    }

    public function show(string $slug): Response
    {
        $post = BlogPost::published()->where('slug', $slug)->firstOrFail();
        $articleText = mb_strtolower(strip_tags($post->title . ' ' . $post->body));
        $matchedCategories = Category::query()
            ->get()
            ->filter(fn (Category $category) => str_contains($articleText, mb_strtolower($category->name)))
            ->pluck('name');
        $relatedStartups = Startup::query()
            ->active()
            ->when($matchedCategories->isNotEmpty(), fn ($query) => $query->whereIn('category', $matchedCategories))
            ->orderByDesc('upvotes')
            ->take(4)
            ->get();
        $relatedPosts = BlogPost::published()
            ->whereKeyNot($post->id)
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        return response()->view('eden.layout', [
            'title' => $post->title,
            'pageTitle' => $post->page_title,
            'metaDescription' => $post->meta_description ?: $post->excerpt,
            'metaKeywords' => $post->meta_keywords,
            'metaImage' => $post->og_image_url,
            'canonicalUrl' => $post->canonical_url,
            'structuredData' => $post->structured_data,
            'ogType' => 'article',
            'ogImageAlt' => $post->title,
            'includeDefaultSiteGraph' => false,
            'content' => view('eden.blog.show', [
                'post' => $post,
                'relatedStartups' => $relatedStartups,
                'relatedPosts' => $relatedPosts,
            ])->render(),
            'scripts' => '',
        ]);
    }
}
