<?php

namespace App\Http\Controllers\Eden;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends EdenController
{
    public function index(Request $request): Response
    {
        $posts = BlogPost::published()
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString();

        $siteName = function_exists('gs') && gs('site_name') ? gs('site_name') : 'Eden';
        $pageTitle = 'Blog — ' . $siteName;
        $metaDescription = function_exists('gs') && gs('meta_description') ? gs('meta_description') : 'Articles and updates from ' . $siteName . '.';

        return response()->view('eden.layout', [
            'title' => 'Blog',
            'pageTitle' => $pageTitle,
            'metaDescription' => $metaDescription,
            'canonicalUrl' => url('/blog'),
            'content' => view('eden.blog.list', ['posts' => $posts])->render(),
            'scripts' => '',
        ]);
    }

    public function show(string $slug): Response
    {
        $post = BlogPost::published()->where('slug', $slug)->firstOrFail();

        return response()->view('eden.layout', [
            'title' => $post->title,
            'pageTitle' => $post->page_title,
            'metaDescription' => $post->meta_description ?: $post->excerpt,
            'metaKeywords' => $post->meta_keywords,
            'metaImage' => $post->og_image_url,
            'canonicalUrl' => $post->canonical_url,
            'structuredData' => $post->structured_data,
            'content' => view('eden.blog.show', ['post' => $post])->render(),
            'scripts' => '',
        ]);
    }
}
