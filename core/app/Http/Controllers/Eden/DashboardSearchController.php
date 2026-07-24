<?php

namespace App\Http\Controllers\Eden;

use App\Http\Controllers\Controller;
use App\Models\AdSpot;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\ContactSubmission;
use App\Models\Startup;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardSearchController extends Controller
{
    private const RESULTS_PER_GROUP = 5;

    public function founder(Request $request): JsonResponse
    {
        $query = $this->validatedQuery($request);
        $startups = Startup::visibleToUser($request->user())
            ->where(function ($builder) use ($query) {
                $builder->where('name', 'like', "%{$query}%")
                    ->orWhere('tagline', 'like', "%{$query}%")
                    ->orWhere('category', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(self::RESULTS_PER_GROUP)
            ->get(['id', 'name', 'slug', 'tagline']);

        return response()->json([
            'groups' => [
                [
                    'label' => 'My apps',
                    'items' => $startups->map(fn (Startup $startup) => [
                        'label' => $startup->name,
                        'description' => $startup->tagline ?: 'Manage app',
                        'url' => route('founder.startups.edit', $startup),
                    ])->values(),
                ],
            ],
        ]);
    }

    public function admin(Request $request): JsonResponse
    {
        $query = $this->validatedQuery($request);
        $admin = auth()->guard('admin')->user();
        $groups = [];

        if ($admin->hasModule('startups')) {
            $groups[] = $this->startupResults($query);
        }
        if ($admin->hasModule('users')) {
            $groups[] = $this->userResults($query);
        }
        if ($admin->hasModule('categories')) {
            $groups[] = $this->categoryResults($query);
        }
        if ($admin->hasModule('blog')) {
            $groups[] = $this->blogResults($query);
        }
        if ($admin->hasModule('messages')) {
            $groups[] = $this->messageResults($query);
        }
        if ($admin->hasModule('advertising')) {
            $groups[] = $this->adResults($query);
        }

        return response()->json(['groups' => array_values($groups)]);
    }

    private function validatedQuery(Request $request): string
    {
        $request->merge([
            'q' => trim((string) $request->input('q', '')),
        ]);
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:80'],
        ]);

        return $validated['q'];
    }

    private function startupResults(string $query): array
    {
        $items = Startup::query()
            ->where(function ($builder) use ($query) {
                $builder->where('name', 'like', "%{$query}%")
                    ->orWhere('tagline', 'like', "%{$query}%")
                    ->orWhere('category', 'like', "%{$query}%")
                    ->orWhere('founder_name', 'like', "%{$query}%")
                    ->orWhere('founder_email', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(self::RESULTS_PER_GROUP)
            ->get();

        return [
            'label' => 'Apps',
            'items' => $items->map(fn (Startup $startup) => [
                'label' => $startup->name,
                'description' => trim(($startup->category ?: 'Uncategorized') . ' · ' . $startup->status),
                'url' => route('admin.startups.edit', $startup),
            ])->values(),
        ];
    }

    private function userResults(string $query): array
    {
        $items = User::query()
            ->where(function ($builder) use ($query) {
                $builder->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(self::RESULTS_PER_GROUP)
            ->get();

        return [
            'label' => 'Users',
            'items' => $items->map(fn (User $user) => [
                'label' => $user->name,
                'description' => $user->email,
                'url' => route('admin.users.startups', $user),
            ])->values(),
        ];
    }

    private function categoryResults(string $query): array
    {
        $items = Category::query()
            ->where('name', 'like', "%{$query}%")
            ->orderBy('name')
            ->limit(self::RESULTS_PER_GROUP)
            ->get();

        return [
            'label' => 'Categories',
            'items' => $items->map(fn (Category $category) => [
                'label' => $category->name,
                'description' => 'Edit category',
                'url' => route('admin.categories.edit', $category),
            ])->values(),
        ];
    }

    private function blogResults(string $query): array
    {
        $items = BlogPost::query()
            ->where(function ($builder) use ($query) {
                $builder->where('title', 'like', "%{$query}%")
                    ->orWhere('excerpt', 'like', "%{$query}%");
            })
            ->orderByDesc('updated_at')
            ->limit(self::RESULTS_PER_GROUP)
            ->get();

        return [
            'label' => 'Blog',
            'items' => $items->map(fn (BlogPost $post) => [
                'label' => $post->title,
                'description' => ucfirst($post->status),
                'url' => route('admin.blog.edit', $post),
            ])->values(),
        ];
    }

    private function messageResults(string $query): array
    {
        $items = ContactSubmission::query()
            ->where(function ($builder) use ($query) {
                $builder->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('subject', 'like', "%{$query}%")
                    ->orWhere('message', 'like', "%{$query}%");
            })
            ->orderByDesc('created_at')
            ->limit(self::RESULTS_PER_GROUP)
            ->get();

        return [
            'label' => 'Messages',
            'items' => $items->map(fn (ContactSubmission $message) => [
                'label' => $message->subject ?: 'Message from ' . $message->name,
                'description' => $message->email,
                'url' => route('admin.contact-messages.show', $message),
            ])->values(),
        ];
    }

    private function adResults(string $query): array
    {
        $items = AdSpot::query()
            ->where(function ($builder) use ($query) {
                $builder->where('placement', 'like', "%{$query}%")
                    ->orWhere('contact_email', 'like', "%{$query}%")
                    ->orWhere('target_url', 'like', "%{$query}%");
            })
            ->orderByDesc('created_at')
            ->limit(self::RESULTS_PER_GROUP)
            ->get();

        return [
            'label' => 'Ads',
            'items' => $items->map(fn (AdSpot $ad) => [
                'label' => $ad->placement,
                'description' => ucfirst($ad->status) . ($ad->contact_email ? ' · ' . $ad->contact_email : ''),
                'url' => route('admin.ad-spots.index', ['placement' => $ad->placement]),
            ])->values(),
        ];
    }
}
