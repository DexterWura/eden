<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Startup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class MyBlogController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', BlogPost::class);
        $posts = $request->user()->blogPosts()->latest('updated_at')->paginate(15);

        return view('my.blog.index', ['posts' => $posts]);
    }

    public function create(): View
    {
        $this->authorize('create', BlogPost::class);
        $startups = $this->userStartups();

        return view('my.blog.create', ['startups' => $startups]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', BlogPost::class);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug',
            'body' => 'required|string',
            'meta_description' => 'nullable|string|max:320',
            'status' => 'required|in:draft,published',
            'startup_id' => 'nullable|exists:startups,id',
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['published_at'] = $validated['status'] === 'published' ? now() : null;

        $post = BlogPost::create($validated);

        return redirect()->route('my.blog.index')->with('success', 'Post saved.');
    }

    public function edit(BlogPost $post): View
    {
        $this->authorize('update', $post);
        $startups = $this->userStartups();

        return view('my.blog.edit', ['post' => $post, 'startups' => $startups]);
    }

    public function update(Request $request, BlogPost $post): RedirectResponse
    {
        $this->authorize('update', $post);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug,' . $post->id,
            'body' => 'required|string',
            'meta_description' => 'nullable|string|max:320',
            'status' => 'required|in:draft,published',
            'startup_id' => 'nullable|exists:startups,id',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['published_at'] = $validated['status'] === 'published'
            ? ($post->published_at ?? now())
            : null;

        $post->update($validated);

        return redirect()->route('my.blog.index')->with('success', 'Post updated.');
    }

    public function destroy(BlogPost $post): RedirectResponse
    {
        $this->authorize('delete', $post);
        $post->delete();

        return redirect()->route('my.blog.index')->with('success', 'Post deleted.');
    }

    protected function userStartups()
    {
        return Startup::where('user_id', auth()->id())->orderBy('name')->get();
    }
}
