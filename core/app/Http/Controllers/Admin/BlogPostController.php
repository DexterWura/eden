<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Rules\SensibleDisplayName;
use App\Rules\SensibleShortText;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogPostController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogPost::query()->orderByDesc('updated_at');
        $statusFilter = $request->get('status', '');
        if ($statusFilter === 'published') {
            $query->published();
        } elseif ($statusFilter === 'draft') {
            $query->where('status', BlogPost::STATUS_DRAFT);
        }
        $search = $request->get('q', '');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('excerpt', 'like', '%' . $search . '%');
            });
        }
        $posts = $query->paginate(20)->withQueryString();

        $content = view('eden.blog.index', [
            'posts' => $posts,
            'statusFilter' => $statusFilter,
            'search' => $search,
            'countPublished' => BlogPost::published()->count(),
            'countDraft' => BlogPost::where('status', BlogPost::STATUS_DRAFT)->count(),
        ])->render();

        return response()->view('eden.layout-dashboard', $this->dashboardVars('Blog', 'blog', $content));
    }

    public function create()
    {
        $post = new BlogPost(['status' => BlogPost::STATUS_DRAFT]);
        $content = view('eden.blog.form', ['post' => $post])->render();
        return response()->view('eden.layout-dashboard', $this->dashboardVars('New post', 'blog', $content));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePost($request);
        $data['slug'] = $this->uniqueSlug(Str::slug($data['title']));
        $data['author_id'] = auth()->guard('admin')->id();
        $data['status'] = $request->input('status', BlogPost::STATUS_DRAFT);
        $data['published_at'] = ($data['status'] === BlogPost::STATUS_PUBLISHED) ? now() : null;
        $post = BlogPost::create($data);
        $this->handleOgImage($request, $post);
        return redirect()->route('admin.blog.index')->with('notify', [['success', 'Post created.']]);
    }

    public function edit(BlogPost $post)
    {
        $content = view('eden.blog.form', ['post' => $post])->render();
        return response()->view('eden.layout-dashboard', $this->dashboardVars('Edit post', 'blog', $content));
    }

    public function update(Request $request, BlogPost $post): RedirectResponse
    {
        $data = $this->validatePost($request);
        $data['slug'] = $this->uniqueSlug(Str::slug($data['title']), $post->id);
        $data['status'] = $request->input('status', $post->status);
        $data['published_at'] = ($data['status'] === BlogPost::STATUS_PUBLISHED && !$post->published_at)
            ? now()
            : $post->published_at;
        $post->update($data);
        $this->handleOgImage($request, $post);
        return redirect()->route('admin.blog.index')->with('notify', [['success', 'Post updated.']]);
    }

    public function destroy(BlogPost $post): RedirectResponse
    {
        $post->delete();
        return redirect()->route('admin.blog.index')->with('notify', [['success', 'Post deleted.']]);
    }

    private function validatePost(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:120', new SensibleDisplayName()],
            'excerpt' => ['nullable', 'string', 'max:500', new SensibleShortText(500)],
            'body' => 'required|string|max:100000',
            'meta_title' => ['nullable', 'string', 'max:70', new SensibleShortText(70)],
            'meta_description' => ['nullable', 'string', 'max:160', new SensibleShortText(160)],
            'meta_keywords' => ['nullable', 'string', 'max:255', new SensibleShortText(255)],
        ]);
    }

    private function uniqueSlug(string $base, ?int $excludeId = null): string
    {
        $slug = $base;
        $n = 0;
        $query = BlogPost::where('slug', $slug);
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }
        while ($query->exists()) {
            $n++;
            $slug = $base . '-' . $n;
            $query = BlogPost::where('slug', $slug);
            if ($excludeId !== null) {
                $query->where('id', '!=', $excludeId);
            }
        }
        return $slug;
    }

    private function handleOgImage(Request $request, BlogPost $post): void
    {
        if (!$request->hasFile('og_image') || !$request->file('og_image')->isValid()) {
            return;
        }
        $dir = public_path('images/blog');
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $file = $request->file('og_image');
        $filename = 'og-' . $post->id . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($dir, $filename);
        $post->update(['og_image_path' => 'images/blog/' . $filename]);
    }

    private function dashboardVars(string $title, string $activeNav, string $content): array
    {
        return [
            'title' => $title,
            'sidebar' => 'admin',
            'activeNav' => $activeNav,
            'dashboardLogo' => (function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden') . ' Admin',
            'dashboardTopbar' => '',
            'searchPlaceholder' => 'Search…',
            'avatarTitle' => 'Admin',
            'avatarLetter' => 'A',
            'content' => $content,
            'scriptDeps' => '<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>',
            'notifyPartial' => view('partials.notify')->render(),
        ];
    }
}
