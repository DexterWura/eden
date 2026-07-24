<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Startup;
use App\Models\StartupUpvote;
use App\Models\User;
use App\Rules\SensibleDisplayName;
use App\Rules\SensiblePersonName;
use App\Rules\SensibleShortText;
use App\Services\StartupActivationService;
use App\Services\StartupFormService;
use App\Support\StartupContentPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StartupController extends Controller
{
    public function __construct(
        private StartupFormService $startupFormService,
        private StartupActivationService $startupActivationService
    ) {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $query = Startup::query()->orderByDesc('updated_at');
        $statusFilter = $request->get('status', '');
        if ($statusFilter === 'pending') {
            $query->pending();
        } elseif ($statusFilter === 'active') {
            $query->active();
        } elseif ($statusFilter === 'disabled') {
            $query->disabled();
        } elseif ($statusFilter === 'banned') {
            $query->banned();
        } elseif ($statusFilter === 'dormant') {
            $query->dormant();
        }
        $search = $request->get('q', '');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('founder_name', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%');
            });
        }
        $qualityFilter = $request->get('quality', '');
        if ($qualityFilter === 'needs-enrichment') {
            $query->needsEnrichment();
        } elseif ($qualityFilter === 'reviewed') {
            $query->whereNotNull('editorial_reviewed_at');
        }
        $startups = $query->paginate(20)->withQueryString();

        foreach ($startups as $s) {
            $s->hasLinkedInFounders = ! empty($this->getFoundersWithLinkedIn($s));
        }

        session(['admin_last_saw_startups' => now()->toDateTimeString()]);

        $content = view('eden.startups.index', [
            'startups' => $startups,
            'statusFilter' => $statusFilter,
            'qualityFilter' => $qualityFilter,
            'search' => $search,
            'countPending' => Startup::pending()->count(),
            'countActive' => Startup::active()->count(),
            'countDisabled' => Startup::disabled()->count(),
            'countBanned' => Startup::banned()->count(),
            'countDormant' => Startup::dormant()->count(),
            'countFeatured' => Startup::featured()->count(),
            'countNeedsEnrichment' => Startup::query()->needsEnrichment()->count(),
        ])->render();

        return response()->view('eden.layout-dashboard', [
            'title' => 'Apps',
            'sidebar' => 'admin',
            'activeNav' => 'startups',
            'dashboardLogo' => (function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden') . ' Admin',
            'dashboardTopbar' => '<button type="button" class="dash-account" title="Property">All apps</button>',
            'searchPlaceholder' => "Try searching 'apps by category'",
            'avatarTitle' => 'Admin',
            'avatarLetter' => 'A',
            'content' => $content,
            'scriptDeps' => '<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>',
            'notifyPartial' => view('partials.notify')->render(),
        ]);
    }

    public function create()
    {
        $startup = new Startup(['status' => Startup::STATUS_ACTIVE]);
        return $this->formResponse('Add app', $startup);
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->validationRules(null));
        if ($validator->fails()) {
            return redirect()->route('admin.startups.create')
                ->withErrors($validator)
                ->withInput();
        }
        $data = $this->startupFormService->prepareValidatedData($validator->validated(), true);
        $data['slug'] = Startup::uniqueSlug($data['name'], null, true);
        $data['status'] = $data['status'] ?? Startup::STATUS_ACTIVE;
        $data['is_featured'] = $request->boolean('is_featured');
        $founders = $this->startupFormService->buildFounders($request, null, null, true);
        $data = $this->startupFormService->applyFounderColumns($data, $founders);
        $data['content_quality_version'] = 1;
        $startup = Startup::create($data);
        $this->startupFormService->processUploadedFiles($request, $startup);
        return redirect()->route('admin.startups.index')
            ->with('notify', [['success', 'App created.']]);
    }

    public function edit(Startup $startup)
    {
        return $this->formResponse('Edit app', $startup);
    }

    public function update(Request $request, Startup $startup): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->validationRules($startup->id));
        if ($validator->fails()) {
            return redirect()->route('admin.startups.edit', $startup)
                ->withErrors($validator)
                ->withInput();
        }
        $data = $this->startupFormService->prepareValidatedData($validator->validated(), true);
        $data['slug'] = Startup::uniqueSlug($data['name'], $startup->id, true);
        $data['is_featured'] = $request->boolean('is_featured');
        $founders = $this->startupFormService->buildFounders($request, $startup, null, true);
        $data = $this->startupFormService->applyFounderColumns($data, $founders, $startup);
        if (($data['status'] ?? $startup->status) === Startup::STATUS_ACTIVE) {
            $data['dormant_at'] = null;
        }
        $previousStatus = $startup->status;
        $startup->update($data);
        $this->startupActivationService->sendTransitionNotifications($startup->fresh(), $previousStatus);
        $this->startupFormService->processUploadedFiles($request, $startup);
        $startup->refresh();
        $startup->promoteContentQualityIfReady();
        return redirect()->route('admin.startups.index')
            ->with('notify', [['success', 'App updated.']]);
    }

    public function disable(Startup $startup)
    {
        $startup->update(['status' => Startup::STATUS_DISABLED]);
        return response()->json(['status' => 'success', 'message' => 'App disabled.']);
    }

    public function activate(Startup $startup)
    {
        $result = $this->startupActivationService->activate($startup);
        if (! $result['activated']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['message'],
            ], 422);
        }

        return response()->json(['status' => 'success', 'message' => $result['message']]);
    }

    public function ban(Startup $startup)
    {
        $startup->update(['status' => Startup::STATUS_BANNED]);
        return response()->json(['status' => 'success', 'message' => 'App banned.']);
    }

    public function unban(Startup $startup)
    {
        $startup->update(['status' => Startup::STATUS_ACTIVE]);
        return response()->json(['status' => 'success', 'message' => 'App unbanned.']);
    }

    public function toggleFeatured(Startup $startup)
    {
        $startup->update(['is_featured' => !$startup->is_featured]);
        $label = $startup->is_featured ? 'Featured' : 'Unfeatured';
        return response()->json(['status' => 'success', 'message' => "App {$label}.", 'is_featured' => $startup->is_featured]);
    }

    public function addUpvotes(Request $request, Startup $startup): JsonResponse
    {
        $validated = $request->validate([
            'count' => ['required', 'integer', 'min:1', 'max:500'],
        ]);

        $requested = (int) $validated['count'];
        $alreadyUpvotedUserIds = StartupUpvote::where('startup_id', $startup->id)->pluck('user_id');
        $candidateUsers = User::query()
            ->whereNotIn('id', $alreadyUpvotedUserIds)
            ->inRandomOrder()
            ->limit($requested)
            ->pluck('id');

        if ($candidateUsers->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No eligible users are available to add legitimate upvotes.',
            ], 422);
        }

        $added = 0;
        DB::transaction(function () use ($startup, $candidateUsers, &$added) {
            foreach ($candidateUsers as $userId) {
                $upvote = StartupUpvote::firstOrCreate([
                    'startup_id' => $startup->id,
                    'user_id' => $userId,
                ]);
                if ($upvote->wasRecentlyCreated) {
                    $added++;
                }
            }

            $startup->upvotes = StartupUpvote::where('startup_id', $startup->id)->count();
            $startup->save();
        });

        $shortage = max(0, $requested - $added);
        $message = $shortage > 0
            ? "Added {$added} legitimate upvotes ({$shortage} fewer than requested due to eligible user limits)."
            : "Added {$added} legitimate upvotes.";

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'added' => $added,
            'upvotes' => (int) $startup->upvotes,
        ]);
    }

    public function toggleFeaturedOnHero(Startup $startup): RedirectResponse
    {
        if (! $startup->featured_on_hero) {
            $linkedinUrls = collect($startup->founders_display)
                ->pluck('linkedin_url')
                ->filter(fn ($url) => trim($url ?? '') !== '')
                ->map(fn ($url) => rtrim(trim($url), '/'))
                ->unique();

            if ($linkedinUrls->isNotEmpty()) {
                $alreadyFeatured = Startup::where('featured_on_hero', true)
                    ->where('id', '!=', $startup->id)
                    ->get();

                $taken = collect();
                foreach ($alreadyFeatured as $other) {
                    foreach ($other->founders_display as $f) {
                        $li = rtrim(trim($f['linkedin_url'] ?? ''), '/');
                        if ($li !== '') {
                            $taken->push($li);
                        }
                    }
                }

                $duplicates = $linkedinUrls->intersect($taken);
                if ($duplicates->isNotEmpty()) {
                    $names = collect($startup->founders_display)
                        ->filter(fn ($f) => $duplicates->contains(rtrim(trim($f['linkedin_url'] ?? ''), '/')))
                        ->pluck('name')
                        ->implode(', ');

                    return redirect()->route('admin.startups.index')
                        ->with('notify', [['error', ($names ?: 'A founder') . ' is already featured on hero via another app.']]);
                }
            }
        }

        $startup->update(['featured_on_hero' => ! $startup->featured_on_hero]);
        $label = $startup->featured_on_hero ? 'featured on hero' : 'removed from hero';
        return redirect()->route('admin.startups.index')
            ->with('notify', [['success', $startup->name . ' ' . $label . '.']]);
    }

    public function destroy(Startup $startup): JsonResponse
    {
        if (!$startup->isDisabled()) {
            return response()->json(['status' => 'error', 'message' => 'Only disabled apps can be deleted.'], 422);
        }
        StartupUpvote::where('startup_id', $startup->id)->delete();
        $startup->delete();
        return response()->json(['status' => 'success', 'message' => 'App deleted.']);
    }

    private function formResponse(string $title, Startup $startup)
    {
        $categories = Category::orderBy('sort_order')->get();
        $content = view('eden.startups.form', [
            'startup' => $startup,
            'categories' => $categories,
            'requiresEditorialContent' => $startup->requiresEditorialContent(),
        ])->render();
        return response()->view('eden.layout-dashboard', [
            'title' => $title,
            'sidebar' => 'admin',
            'activeNav' => 'startups',
            'dashboardLogo' => (function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden') . ' Admin',
            'dashboardTopbar' => '<button type="button" class="dash-account" title="Property">All apps</button>',
            'searchPlaceholder' => "Try searching 'apps by category'",
            'avatarTitle' => 'Admin',
            'avatarLetter' => 'A',
            'content' => $content,
            'scriptDeps' => '<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>',
            'notifyPartial' => view('partials.notify')->render(),
        ]);
    }

    private function getFoundersWithLinkedIn(Startup $startup): array
    {
        $result = [];
        $founders = $startup->founders ?? [];
        foreach ($founders as $f) {
            $li = is_array($f) ? ($f['linkedin_url'] ?? null) : ($f->linkedin_url ?? null);
            if (! empty(trim((string) ($li ?? '')))) {
                $result[] = [
                    'name' => is_array($f) ? ($f['name'] ?? '') : ($f->name ?? ''),
                    'email' => is_array($f) ? ($f['email'] ?? null) : ($f->email ?? null),
                    'linkedin_url' => trim((string) $li),
                ];
            }
        }
        if (empty($result) && ! empty(trim((string) ($startup->founder_linkedin_url ?? '')))) {
            $result[] = [
                'name' => $startup->founder_name ?? '',
                'email' => $startup->founder_email ?? null,
                'linkedin_url' => trim((string) $startup->founder_linkedin_url),
            ];
        }
        return $result;
    }

    private function validationRules(?int $excludeId = null): array
    {
        $startup = $excludeId === null ? new Startup() : Startup::query()->find($excludeId);
        $requiresEditorialContent = $startup?->requiresEditorialContent() ?? true;
        $editorialPresenceRule = $requiresEditorialContent ? 'required' : 'nullable';

        return [
            'name' => ['required', 'string', 'max:120', new SensibleDisplayName(), function ($attr, $value, $fail) use ($excludeId) {
                if ($value && Startup::listingNameExistsForAnother($value, $excludeId)) {
                    $fail(__('A listing with this name already exists.'));
                }
            }],
            'tagline' => [$editorialPresenceRule, 'string', ...($requiresEditorialContent ? ['min:12'] : []), 'max:255', new SensibleShortText(255)],
            'description' => [$editorialPresenceRule, 'string', ...($requiresEditorialContent ? ['min:' . StartupContentPolicy::DESCRIPTION_MIN] : []), 'max:10000'],
            'problem_solved' => [$editorialPresenceRule, 'string', ...($requiresEditorialContent ? ['min:' . StartupContentPolicy::PROBLEM_SOLVED_MIN] : []), 'max:3000'],
            'target_customer' => [$editorialPresenceRule, 'string', ...($requiresEditorialContent ? ['min:' . StartupContentPolicy::TARGET_CUSTOMER_MIN] : []), 'max:1500'],
            'key_features' => [$editorialPresenceRule, 'array', 'min:' . StartupContentPolicy::KEY_FEATURES_MIN, 'max:8'],
            'key_features.*' => [$editorialPresenceRule, 'string', ...($requiresEditorialContent ? ['min:5'] : []), 'max:180', new SensibleShortText(180)],
            'pricing_model' => ['nullable', 'string', 'max:120', new SensibleShortText(120)],
            'markets_served' => ['nullable', 'string', 'max:500', new SensibleShortText(500)],
            'traction' => ['nullable', 'string', 'max:3000'],
            'founder_story' => ['nullable', 'string', 'max:5000'],
            'editorial_reviewed_at' => ['nullable', 'date'],
            'category' => ['nullable', 'string', 'exists:categories,name'],
            'website' => ['nullable', 'string', 'max:500', 'url', function ($attr, $value, $fail) use ($excludeId) {
                if ($value && Startup::websiteExistsForAnother($value, $excludeId)) {
                    $fail('An app with this website link already exists.');
                }
            }],
            'location' => ['nullable', 'string', 'max:255', new SensibleShortText(255)],
            'founder_email' => ['nullable', 'email', 'max:255'],
            'founder_twitter_url' => ['nullable', 'string', 'max:255'],
            'founder_linkedin_url' => ['nullable', 'string', 'max:500', 'url'],
            'launch_date' => ['nullable', 'date'],
            'twitter_url' => ['nullable', 'string', 'max:255'],
            'linkedin_url' => ['nullable', 'string', 'max:500', 'url'],
            'status' => ['nullable', 'in:pending,active,disabled,banned,dormant'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:2048'],
            'founders_names' => ['nullable', 'array'],
            'founders_names.*' => ['nullable', 'string', 'max:80', new SensiblePersonName()],
            'founders_emails' => ['nullable', 'array'],
            'founders_emails.*' => ['nullable', 'email', 'max:255'],
            'founders_twitter_urls' => ['nullable', 'array'],
            'founders_twitter_urls.*' => ['nullable', 'string', 'max:255'],
            'founders_linkedin_urls' => ['nullable', 'array'],
            'founders_linkedin_urls.*' => ['nullable', 'string', 'max:500', 'url'],
            'founders_photo_urls' => ['nullable', 'array'],
            'founders_photo_urls.*' => ['nullable', 'string', 'max:500'],
            'founders_photos' => ['nullable', 'array'],
            'founders_photos.*' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:2048'],
            'product_images' => ['nullable', 'array'],
            'product_images.*' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:2048'],
            'mrr' => ['nullable', 'numeric', 'min:0'],
            'revenue' => ['nullable', 'numeric', 'min:0'],
            'views' => ['nullable', 'integer', 'min:0'],
            'clicks' => ['nullable', 'integer', 'min:0'],
        ];
    }

}
