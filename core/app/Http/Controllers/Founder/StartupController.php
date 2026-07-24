<?php

namespace App\Http\Controllers\Founder;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Startup;
use App\Models\StartupFundingRound;
use App\Rules\SensibleDisplayName;
use App\Rules\SensiblePersonName;
use App\Rules\SensibleShortText;
use App\Services\GoogleSearchConsoleService;
use App\Services\StartupFormService;
use App\Services\StartupFundingRoundService;
use App\Support\StartupContentPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StartupController extends Controller
{
    public function __construct(
        private StartupFormService $startupFormService,
        private StartupFundingRoundService $fundingRoundService
    ) {
        parent::__construct();
    }

    public function index()
    {
        $user = auth()->user();
        $startups = Startup::visibleToUser($user)->orderByDesc('updated_at')->get();

        $content = view('eden.founder.startups-index', [
            'startups' => $startups,
            'canAddStartup' => $this->canAddStartup($user),
        ])->render();

        return $this->layoutResponse('My startups', 'startups', $content);
    }

    public function create()
    {
        if (! $this->canAddStartup(auth()->user())) {
            return redirect()->route('pricing')
                ->with('info', 'Free accounts get one startup. Go Pro for unlimited startups, analytics, hero featuring, and more.');
        }
        $startup = new Startup(['status' => Startup::STATUS_ACTIVE]);
        $categories = Category::orderBy('sort_order')->get();
        $content = view('eden.founder.startups-form', [
            'startup' => $startup,
            'categories' => $categories,
            'requiresEditorialContent' => $startup->requiresEditorialContent(),
        ])->render();
        return $this->layoutResponse('Add startup', 'startups', $content, true);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $this->canAddStartup(auth()->user())) {
            return redirect()->route('pricing')
                ->with('info', 'Free accounts get one startup. Go Pro for unlimited startups, analytics, hero featuring, and more.');
        }
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return redirect()->route('founder.startups.create')->withErrors($validator)->withInput();
        }
        $data = $this->startupFormService->prepareValidatedData($validator->validated());
        $data['traffic_tracking_enabled'] = filter_var($data['traffic_tracking_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $data['user_id'] = auth()->id();
        $data['slug'] = Startup::uniqueSlug($data['name'], null, true);
        $data['status'] = Startup::STATUS_PENDING;
        $founders = $this->startupFormService->buildFounders(
            $request,
            null,
            auth()->id()
        );
        $data = $this->startupFormService->applyFounderColumns($data, $founders, null, auth()->user());
        $this->applyFlipitForSaleFromRequest($request, $data);
        $data['content_quality_version'] = 1;
        $startup = Startup::create($data);
        $this->startupFormService->processUploadedFiles($request, $startup);
        return redirect()->route('founder.startups.index')->with('notify', [['success', 'Startup submitted! It will go live once reviewed by our team.']]);
    }

    public function edit(Startup $startup)
    {
        $this->authorize('manage', $startup);
        $categories = Category::orderBy('sort_order')->get();
        $fundingRoundTypes = StartupFundingRound::ROUND_TYPES;
        $content = view('eden.founder.startups-form', [
            'startup' => $startup,
            'categories' => $categories,
            'fundingRoundTypes' => $fundingRoundTypes,
            'requiresEditorialContent' => $startup->requiresEditorialContent(),
        ])->render();
        return $this->layoutResponse('Edit startup', 'startups', $content, true);
    }

    public function update(Request $request, Startup $startup): RedirectResponse
    {
        $this->authorize('manage', $startup);
        $validator = Validator::make($request->all(), $this->rules($startup->id));
        if ($validator->fails()) {
            return redirect()->route('founder.startups.edit', $startup)->withErrors($validator)->withInput();
        }
        $data = $this->startupFormService->prepareValidatedData($validator->validated());
        $data['slug'] = Startup::uniqueSlug($data['name'], $startup->id, true);
        $data['traffic_tracking_enabled'] = filter_var($data['traffic_tracking_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $founders = $this->startupFormService->buildFounders(
            $request,
            $startup,
            auth()->id()
        );
        $data = $this->startupFormService->applyFounderColumns($data, $founders, $startup, auth()->user());
        $this->applyFlipitForSaleFromRequest($request, $data);
        $startup->update($data);
        $this->startupFormService->processUploadedFiles($request, $startup);
        if (auth()->user()->isPro()) {
            $this->fundingRoundService->sync($startup, $request->all());
        }
        $startup->refresh();
        $startup->promoteContentQualityIfReady();
        return redirect()->route('founder.startups.index')->with('notify', [['success', 'Startup updated.']]);
    }

    public function destroy(Startup $startup): RedirectResponse
    {
        $this->authorize('manage', $startup);
        if (!auth()->user()->isPro()) {
            abort(403, 'Pro membership required to delete startups.');
        }
        $startup->delete();
        return redirect()->route('founder.startups.index')->with('notify', [['success', 'Startup deleted.']]);
    }

    public function toggleFeatured(Startup $startup): RedirectResponse
    {
        $this->authorize('manage', $startup);
        if (!auth()->user()->isPro()) {
            abort(403, 'Pro membership required to feature startups.');
        }
        $startup->update(['is_featured' => !$startup->is_featured]);
        $label = $startup->is_featured ? 'featured' : 'unfeatured';
        return redirect()->route('founder.startups.index')->with('notify', [['success', "Startup {$label}."]]);
    }

    private function canAddStartup(\App\Models\User $user): bool
    {
        if ($user->isPro()) {
            return true;
        }
        return $user->startups()->count() < 1;
    }

    private function layoutResponse(string $title, string $activeNav, string $content, bool $withFormAssets = false): \Illuminate\Http\Response
    {
        $vars = [
            'title' => $title,
            'sidebar' => 'founder',
            'activeNav' => $activeNav,
            'dashboardLogo' => function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden',
            'dashboardTopbar' => '',
            'searchPlaceholder' => "Search…",
            'avatarTitle' => auth()->user()->name ?? 'Account',
            'avatarLetter' => strtoupper(mb_substr(auth()->user()->name ?? '?', 0, 1)),
            'content' => $content,
        ];
        if ($withFormAssets) {
            $vars['scriptDeps'] = '<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>';
            $vars['notifyPartial'] = view('partials.notify')->render();
        }
        return response()->view('eden.layout-dashboard', $vars);
    }

    private function rules(?int $excludeId = null): array
    {
        $gscService = app(GoogleSearchConsoleService::class);
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
            'category' => ['nullable', 'string', 'exists:categories,name'],
            'website' => ['nullable', 'string', 'max:500', 'url', function ($attr, $value, $fail) use ($excludeId) {
                if ($value && Startup::websiteExistsForAnother($value, $excludeId)) {
                    $fail('A startup with this website link already exists.');
                }
            }],
            'location' => ['nullable', 'string', 'max:255', new SensibleShortText(255)],
            'founder_email' => ['nullable', 'email', 'max:255'],
            'founder_twitter_url' => ['nullable', 'string', 'max:255'],
            'founder_linkedin_url' => ['nullable', 'string', 'max:500', 'url'],
            'launch_date' => ['nullable', 'date'],
            'twitter_url' => ['nullable', 'string', 'max:255'],
            'linkedin_url' => ['nullable', 'string', 'max:500', 'url'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:2048'],
            'founders_names' => ['nullable', 'array'],
            'founders_names.*' => ['nullable', 'string', 'max:80', new SensiblePersonName()],
            'founders_emails' => ['nullable', 'array'],
            'founders_emails.*' => ['nullable', 'email', 'max:255'],
            'founders_twitter_urls' => ['nullable', 'array'],
            'founders_twitter_urls.*' => ['nullable', 'string', 'max:255'],
            'founders_linkedin_urls' => ['nullable', 'array'],
            'founders_linkedin_urls.*' => ['nullable', 'string', 'max:500', 'url'],
            'founders_photos' => ['nullable', 'array'],
            'founders_photos.*' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:2048'],
            'product_images' => ['nullable', 'array'],
            'product_images.*' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:2048'],
            'mrr' => ['nullable', 'numeric', 'min:0'],
            'revenue' => ['nullable', 'numeric', 'min:0'],
            'traffic_tracking_enabled' => ['nullable', 'boolean'],
            'search_console_property' => [
                'nullable',
                'string',
                'max:500',
                function (string $attribute, mixed $value, \Closure $fail) use ($gscService): void {
                    $value = is_string($value) ? trim($value) : '';
                    if ($value === '' || ! $gscService->isConfigured()) {
                        return;
                    }
                    if (! $gscService->verifyPropertyAccessible($value)) {
                        $fail('Could not verify access to this Google Search Console property. Make sure the API key is configured and the property is shared with the API project.');
                    }
                },
            ],
            'for_sale' => ['nullable', 'boolean'],
            'flipit_listing_url' => [
                'nullable',
                'string',
                'max:500',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || trim((string) $value) === '') {
                        return;
                    }
                    $v = trim((string) $value);
                    if (Startup::isFlipitListingNumber($v)) {
                        return;
                    }
                    if (Startup::flipitListingIdFromUrl($v) !== null) {
                        return;
                    }
                    $fail('The FLIPit listing must be a valid listing number (12 characters) or a full FLIPit listing URL.');
                },
            ],
            'seeking_investors' => ['nullable', 'in:0,1'],
            'funding_round_type' => ['nullable', 'string', 'in:' . implode(',', array_keys(StartupFundingRound::ROUND_TYPES))],
            'funding_amount_seeking' => ['nullable', 'numeric', 'min:0'],
            'funding_currency' => ['nullable', 'string', 'max:3'],
            'funding_contact_email' => ['nullable', 'email', 'max:255'],
            'funding_description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    private function applyFlipitForSaleFromRequest(Request $request, array &$data): void
    {
        unset($data['flipit_listing_url']);
        $forSale = filter_var($data['for_sale'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $data['for_sale'] = $forSale;
        if ($forSale) {
            $value = $request->input('flipit_listing_url');
            $value = $value ? trim($value) : null;
            if ($value === null || $value === '') {
                $data['flipit_listing_id'] = null;
            } elseif (Startup::isFlipitListingNumber($value)) {
                $data['flipit_listing_id'] = strtoupper($value);
            } else {
                $data['flipit_listing_id'] = Startup::flipitListingIdFromUrl($value);
            }
        } else {
            $data['flipit_listing_id'] = null;
        }
    }
}
