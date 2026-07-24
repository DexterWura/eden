<?php

namespace App\Http\Controllers\Founder;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Rules\SensibleDisplayName;
use App\Rules\SensibleShortText;
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
            'title' => ['required', 'string', 'max:120', new SensibleDisplayName()],
            'body' => 'required|string|min:500|max:100000',
            'excerpt' => ['nullable', 'string', 'max:500', new SensibleShortText(500)],
            'og_image' => 'nullable|image|mimes:jpeg,png,gif,webp|max:4096',
        ]);

        $slug = Str::slug($validated['title']);
        if (BlogPost::where('slug', $slug)->exists()) {
            $slug .= '-' . Str::random(4);
        }

        $post = BlogPost::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'body' => $this->sanitizeArticleBody($validated['body']),
            'excerpt' => $validated['excerpt'] ?? Str::limit(strip_tags($validated['body']), 160),
            'author_id' => auth()->id(),
            'author_type' => 'user',
            'status' => BlogPost::STATUS_DRAFT,
            'published_at' => null,
            'editorial_reviewed_at' => null,
        ]);
        $this->handleOgImage($request, $post);

        return redirect()->route('founder.blog.index')->with('notify', [['success', 'Blog post submitted for editorial review.']]);
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
            'title' => ['required', 'string', 'max:120', new SensibleDisplayName()],
            'body' => 'required|string|min:500|max:100000',
            'excerpt' => ['nullable', 'string', 'max:500', new SensibleShortText(500)],
            'og_image' => 'nullable|image|mimes:jpeg,png,gif,webp|max:4096',
        ]);

        $post->update([
            'title' => $validated['title'],
            'body' => $this->sanitizeArticleBody($validated['body']),
            'excerpt' => $validated['excerpt'] ?? Str::limit(strip_tags($validated['body']), 160),
            'status' => BlogPost::STATUS_DRAFT,
            'published_at' => null,
            'editorial_reviewed_at' => null,
        ]);
        $this->handleOgImage($request, $post);

        return redirect()->route('founder.blog.index')->with('notify', [['success', 'Changes saved and returned to editorial review.']]);
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

    private function sanitizeArticleBody(string $body): string
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set(
            'HTML.Allowed',
            'p,br,h2,h3,h4,strong,b,em,i,ul,ol,li,blockquote,a[href|title|target|rel],figure,figcaption,img[src|alt|width|height],code,pre'
        );
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $purifier = new \HTMLPurifier($config);

        return $purifier->purify($body);
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
