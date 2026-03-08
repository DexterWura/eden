<?php

namespace App\Http\Controllers\Founder;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Startup;
use App\Models\StartupFundingRound;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StartupController extends Controller
{
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
        $content = view('eden.founder.startups-form', ['startup' => $startup, 'categories' => $categories])->render();
        return $this->layoutResponse('Add startup', 'startups', $content, true);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $this->canAddStartup(auth()->user())) {
            return redirect()->route('pricing')
                ->with('info', 'Free accounts get one startup. Go Pro for unlimited startups, analytics, hero featuring, and more.');
        }
        $validator = Validator::make($request->all(), $this->rules(), $this->messages());
        if ($validator->fails()) {
            return redirect()->route('founder.startups.create')->withErrors($validator)->withInput();
        }
        $data = $validator->validated();
        unset($data['logo'], $data['founders_names'], $data['founders_emails'], $data['founders_twitter_urls'], $data['founders_linkedin_urls'], $data['founders_photos'], $data['product_images']);
        $data['traffic_tracking_enabled'] = filter_var($data['traffic_tracking_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $data['user_id'] = auth()->id();
        $data['slug'] = Str::slug($data['name']);
        if (Startup::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $data['slug'] . '-' . Str::random(4);
        }
        $data['status'] = Startup::STATUS_PENDING;
        $data['founders'] = $this->buildFoundersFromRequest($request);
        $first = $this->firstFounderData($data['founders']);
        $data['founder_name'] = $first['name'] ?? auth()->user()->name;
        $data['founder_email'] = $first['email'] ?? auth()->user()->email;
        $data['founder_twitter_url'] = $first['twitter_url'] ?? null;
        $data['founder_linkedin_url'] = $first['linkedin_url'] ?? null;
        $data['twitter_url'] = $this->normalizeTwitterInput($data['twitter_url'] ?? null);
        $this->applyFlipitForSaleFromRequest($request, $data);
        $startup = Startup::create($data);
        $this->processStartupFiles($request, $startup);
        return redirect()->route('founder.startups.index')->with('notify', [['success', 'Startup submitted! It will go live once reviewed by our team.']]);
    }

    public function edit(Startup $startup)
    {
        $this->authorizeStartup($startup);
        $categories = Category::orderBy('sort_order')->get();
        $fundingRoundTypes = StartupFundingRound::ROUND_TYPES;
        $content = view('eden.founder.startups-form', [
            'startup' => $startup,
            'categories' => $categories,
            'fundingRoundTypes' => $fundingRoundTypes,
        ])->render();
        return $this->layoutResponse('Edit startup', 'startups', $content, true);
    }

    public function update(Request $request, Startup $startup): RedirectResponse
    {
        $this->authorizeStartup($startup);
        $validator = Validator::make($request->all(), $this->rules($startup->id), $this->messages());
        if ($validator->fails()) {
            return redirect()->route('founder.startups.edit', $startup)->withErrors($validator)->withInput();
        }
        $data = $validator->validated();
        $data['slug'] = Str::slug($data['name']);
        if (Startup::where('slug', $data['slug'])->where('id', '!=', $startup->id)->exists()) {
            $data['slug'] = $data['slug'] . '-' . Str::random(4);
        }
        unset($data['logo'], $data['founders_names'], $data['founders_emails'], $data['founders_twitter_urls'], $data['founders_linkedin_urls'], $data['founders_photos'], $data['product_images']);
        $data['traffic_tracking_enabled'] = filter_var($data['traffic_tracking_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $data['founders'] = $this->buildFoundersFromRequest($request, $startup);
        $first = $this->firstFounderData($data['founders']);
        $data['founder_name'] = $first['name'] ?? $startup->founder_name;
        $data['founder_email'] = $first['email'] ?? $startup->founder_email;
        $data['founder_twitter_url'] = $first['twitter_url'] ?? $startup->founder_twitter_url;
        $data['founder_linkedin_url'] = $first['linkedin_url'] ?? $startup->founder_linkedin_url;
        $data['twitter_url'] = $this->normalizeTwitterInput($data['twitter_url'] ?? null);
        $this->applyFlipitForSaleFromRequest($request, $data);
        $startup->update($data);
        $this->processStartupFiles($request, $startup);
        $this->applyFundingRoundFromRequest($request, $startup);
        return redirect()->route('founder.startups.index')->with('notify', [['success', 'Startup updated.']]);
    }

    public function destroy(Startup $startup): RedirectResponse
    {
        $this->authorizeStartup($startup);
        if (!auth()->user()->isPro()) {
            abort(403, 'Pro membership required to delete startups.');
        }
        $startup->delete();
        return redirect()->route('founder.startups.index')->with('notify', [['success', 'Startup deleted.']]);
    }

    public function toggleFeatured(Startup $startup): RedirectResponse
    {
        $this->authorizeStartup($startup);
        if (!auth()->user()->isPro()) {
            abort(403, 'Pro membership required to feature startups.');
        }
        $startup->update(['is_featured' => !$startup->is_featured]);
        $label = $startup->is_featured ? 'featured' : 'unfeatured';
        return redirect()->route('founder.startups.index')->with('notify', [['success', "Startup {$label}."]]);
    }

    private function buildFoundersFromRequest(Request $request, ?Startup $startup = null): array
    {
        $names = $request->input('founders_names', []);
        $emails = $request->input('founders_emails', []);
        $twitterUrls = $request->input('founders_twitter_urls', []);
        $linkedinUrls = $request->input('founders_linkedin_urls', []);
        $photos = $request->file('founders_photos', []);
        $existing = $startup ? ($startup->founders ?? []) : [];
        $founders = [];
        foreach ($names as $i => $name) {
            $name = trim((string) ($name ?? ''));
            if ($name === '') {
                continue;
            }
            $photoUrl = null;
            if (isset($photos[$i]) && $photos[$i]->isValid()) {
                $dir = public_path('images/startups/founders');
                if (! is_dir($dir)) {
                    @mkdir($dir, 0755, true);
                }
                $filename = 'f-' . uniqid() . '-' . $i . '.' . $photos[$i]->getClientOriginalExtension();
                $photos[$i]->move($dir, $filename);
                $photoUrl = 'images/startups/founders/' . $filename;
            } elseif (isset($existing[$i]) && is_array($existing[$i]) && ! empty($existing[$i]['photo_url'])) {
                $photoUrl = $existing[$i]['photo_url'];
            }
            $email = isset($emails[$i]) ? trim((string) $emails[$i]) : null;
            $twitter = isset($twitterUrls[$i]) ? $this->normalizeTwitterInput(trim((string) $twitterUrls[$i])) : null;
            $linkedin = isset($linkedinUrls[$i]) ? trim((string) $linkedinUrls[$i]) : null;
            $founders[] = [
                'name' => $name,
                'photo_url' => $photoUrl,
                'email' => $email !== '' ? $email : null,
                'twitter_url' => $twitter,
                'linkedin_url' => $linkedin !== '' ? $linkedin : null,
            ];
        }
        return $founders;
    }

    private function normalizeTwitterInput(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        $value = trim($value);
        if (str_starts_with(strtolower($value), 'http://') || str_starts_with(strtolower($value), 'https://')) {
            return $value;
        }
        $handle = ltrim($value, '@');
        return 'https://x.com/' . $handle;
    }

    private function firstFounderData(array $founders): array
    {
        foreach ($founders as $f) {
            $name = is_array($f) ? ($f['name'] ?? '') : (is_object($f) ? ($f->name ?? '') : '');
            if ($name !== '') {
                return [
                    'name' => $name,
                    'email' => is_array($f) ? ($f['email'] ?? null) : (is_object($f) ? ($f->email ?? null) : null),
                    'twitter_url' => is_array($f) ? ($f['twitter_url'] ?? null) : (is_object($f) ? ($f->twitter_url ?? null) : null),
                    'linkedin_url' => is_array($f) ? ($f['linkedin_url'] ?? null) : (is_object($f) ? ($f->linkedin_url ?? null) : null),
                ];
            }
        }
        return [];
    }

    private function processStartupFiles(Request $request, Startup $startup): void
    {
        $baseDir = public_path('images/startups/' . $startup->id);
        $updates = [];

        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            if (!is_dir($baseDir)) {
                @mkdir($baseDir, 0755, true);
            }
            $ext = $request->file('logo')->getClientOriginalExtension();
            $request->file('logo')->move($baseDir, 'logo.' . $ext);
            $updates['logo_path'] = 'images/startups/' . $startup->id . '/logo.' . $ext;
        }

        $productFiles = $request->file('product_images', []);
        if (!empty($productFiles)) {
            if (!is_dir($baseDir)) {
                @mkdir($baseDir, 0755, true);
            }
            $productDir = $baseDir . '/products';
            if (!is_dir($productDir)) {
                @mkdir($productDir, 0755, true);
            }
            $existing = $startup->product_images ?? [];
            foreach ($productFiles as $file) {
                if (!$file->isValid()) {
                    continue;
                }
                $filename = 'p-' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($productDir, $filename);
                $existing[] = 'images/startups/' . $startup->id . '/products/' . $filename;
            }
            $updates['product_images'] = $existing;
        }

        if (!empty($updates)) {
            $startup->update($updates);
        }
    }

    private function authorizeStartup(Startup $startup): void
    {
        if (! $startup->userCanManage(auth()->user())) {
            abort(403, 'You do not have permission to manage this startup.');
        }
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'exists:categories,name'],
            'website' => ['nullable', 'string', 'max:500', 'url', function ($attr, $value, $fail) use ($excludeId) {
                if ($value && Startup::websiteExistsForAnother($value, $excludeId)) {
                    $fail('A startup with this website link already exists.');
                }
            }],
            'location' => ['nullable', 'string', 'max:255'],
            'founder_email' => ['nullable', 'email', 'max:255'],
            'founder_twitter_url' => ['nullable', 'string', 'max:255'],
            'founder_linkedin_url' => ['nullable', 'string', 'max:500', 'url'],
            'launch_date' => ['nullable', 'date'],
            'twitter_url' => ['nullable', 'string', 'max:255'],
            'linkedin_url' => ['nullable', 'string', 'max:500', 'url'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:2048'],
            'founders_names' => ['nullable', 'array'],
            'founders_names.*' => ['nullable', 'string', 'max:255'],
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

    private function applyFundingRoundFromRequest(Request $request, Startup $startup): void
    {
        if (! auth()->user()->isPro()) {
            return;
        }

        $seeking = $request->input('seeking_investors') === '1';

        $openRound = $startup->activeFundingRound;

        if (! $seeking) {
            if ($openRound) {
                $openRound->update(['status' => StartupFundingRound::STATUS_CLOSED]);
            }
            return;
        }

        $roundType = $request->input('funding_round_type', 'seed');
        if (! array_key_exists($roundType, StartupFundingRound::ROUND_TYPES)) {
            $roundType = 'seed';
        }

        $payload = [
            'round_type' => $roundType,
            'amount_seeking' => $request->input('funding_amount_seeking') ?: null,
            'currency' => strtoupper(substr($request->input('funding_currency', 'USD'), 0, 3)) ?: 'USD',
            'description' => $request->input('funding_description') ?: null,
            'contact_email' => $request->input('funding_contact_email') ?: null,
            'status' => StartupFundingRound::STATUS_OPEN,
        ];

        if ($openRound) {
            $openRound->update($payload);
        } else {
            StartupFundingRound::create(array_merge(['startup_id' => $startup->id], $payload));
        }
    }

    private function messages(): array
    {
        return [];
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
