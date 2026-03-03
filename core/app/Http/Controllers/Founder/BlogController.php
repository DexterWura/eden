<?php

namespace App\Http\Controllers\Founder;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index()
    {
        $this->requirePro();
        $posts = BlogPost::where('author_id', auth()->id())
            ->orderByDesc('updated_at')
            ->get();

        $content = view('eden.founder.blog-index', ['posts' => $posts])->render();
        return $this->layoutResponse('My blog posts', 'blog', $content);
    }

    public function create()
    {
        $this->requirePro();
        $post = new BlogPost();
        $content = view('eden.founder.blog-form', ['post' => $post])->render();
        return $this->layoutResponse('Write a blog post', 'blog', $content);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requirePro();
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
        ]);

        $slug = Str::slug($validated['title']);
        if (BlogPost::where('slug', $slug)->exists()) {
            $slug .= '-' . Str::random(4);
        }

        BlogPost::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'body' => $validated['body'],
            'excerpt' => $validated['excerpt'] ?? Str::limit(strip_tags($validated['body']), 160),
            'author_id' => auth()->id(),
            'status' => 'published',
            'published_at' => now(),
        ]);

        return redirect()->route('founder.blog.index')->with('notify', [['success', 'Blog post published.']]);
    }

    public function edit(BlogPost $post)
    {
        $this->requirePro();
        $this->authorizePost($post);
        $content = view('eden.founder.blog-form', ['post' => $post])->render();
        return $this->layoutResponse('Edit blog post', 'blog', $content);
    }

    public function update(Request $request, BlogPost $post): RedirectResponse
    {
        $this->requirePro();
        $this->authorizePost($post);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
        ]);

        $post->update([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'excerpt' => $validated['excerpt'] ?? Str::limit(strip_tags($validated['body']), 160),
        ]);

        return redirect()->route('founder.blog.index')->with('notify', [['success', 'Blog post updated.']]);
    }

    public function destroy(BlogPost $post): RedirectResponse
    {
        $this->requirePro();
        $this->authorizePost($post);
        $post->delete();
        return redirect()->route('founder.blog.index')->with('notify', [['success', 'Blog post deleted.']]);
    }

    private function requirePro(): void
    {
        if (!auth()->user()->isPro()) {
            abort(403, 'Pro membership required to write blog posts.');
        }
    }

    private function authorizePost(BlogPost $post): void
    {
        if ((int) $post->author_id !== (int) auth()->id()) {
            abort(403, 'You do not have permission to manage this post.');
        }
    }

    private function layoutResponse(string $title, string $activeNav, string $content): \Illuminate\Http\Response
    {
        return response()->view('eden.layout-dashboard', [
            'title' => $title,
            'sidebar' => 'founder',
            'activeNav' => $activeNav,
            'dashboardLogo' => function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden',
            'dashboardTopbar' => '',
            'searchPlaceholder' => 'Search…',
            'avatarTitle' => auth()->user()->name ?? 'Account',
            'avatarLetter' => strtoupper(mb_substr(auth()->user()->name ?? '?', 0, 1)),
            'content' => $content,
        ]);
    }
}
