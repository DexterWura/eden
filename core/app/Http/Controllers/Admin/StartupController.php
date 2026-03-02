<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Startup;
use App\Models\StartupUpvote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StartupController extends Controller
{
    public function index(Request $request)
    {
        $query = Startup::query()->orderByDesc('updated_at');
        $statusFilter = $request->get('status', '');
        if ($statusFilter === 'active') {
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
        $startups = $query->paginate(20)->withQueryString();

        foreach ($startups as $s) {
            $s->hasLinkedInFounders = ! empty($this->getFoundersWithLinkedIn($s));
        }

        $content = view('eden.startups.index', [
            'startups' => $startups,
            'statusFilter' => $statusFilter,
            'search' => $search,
            'countActive' => Startup::active()->count(),
            'countDisabled' => Startup::disabled()->count(),
            'countBanned' => Startup::banned()->count(),
            'countDormant' => Startup::dormant()->count(),
            'countFeatured' => Startup::featured()->count(),
        ])->render();

        return response()->view('eden.layout-dashboard', [
            'title' => 'Startups',
            'sidebar' => 'admin',
            'activeNav' => 'startups',
            'dashboardLogo' => (function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden') . ' Admin',
            'dashboardTopbar' => '<button type="button" class="dash-account" title="Property">All startups</button>',
            'searchPlaceholder' => "Try searching 'startups by category'",
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
        return $this->formResponse('Add startup', $startup);
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->validationRules(), $this->validationMessages());
        if ($validator->fails()) {
            return redirect()->route('admin.startups.create')
                ->withErrors($validator)
                ->withInput();
        }
        $data = $validator->validated();
        unset($data['logo'], $data['founders_names'], $data['founders_emails'], $data['founders_twitter_urls'], $data['founders_linkedin_urls'], $data['founders_photos'], $data['product_images']);
        $data['slug'] = Str::slug($data['name']);
        if (Startup::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $data['slug'] . '-' . Str::random(4);
        }
        $data['status'] = $data['status'] ?? Startup::STATUS_ACTIVE;
        $data['is_featured'] = $request->boolean('is_featured');
        $data['founders'] = $this->buildFoundersFromRequest($request, null);
        $first = $this->firstFounderData($data['founders']);
        $data['founder_name'] = $first['name'] ?? null;
        $data['founder_email'] = $first['email'] ?? null;
        $data['founder_twitter_url'] = $first['twitter_url'] ?? null;
        $data['founder_linkedin_url'] = $first['linkedin_url'] ?? null;
        $data['twitter_url'] = $this->normalizeTwitterInput($data['twitter_url'] ?? null);
        $startup = Startup::create($data);
        $this->processStartupFiles($request, $startup);
        return redirect()->route('admin.startups.index')
            ->with('notify', [['success', 'Startup created.']]);
    }

    public function edit(Startup $startup)
    {
        return $this->formResponse('Edit startup', $startup);
    }

    public function update(Request $request, Startup $startup): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->validationRules(), $this->validationMessages());
        if ($validator->fails()) {
            return redirect()->route('admin.startups.edit', $startup)
                ->withErrors($validator)
                ->withInput();
        }
        $data = $validator->validated();
        unset($data['logo'], $data['founders_names'], $data['founders_emails'], $data['founders_twitter_urls'], $data['founders_linkedin_urls'], $data['founders_photos'], $data['product_images']);
        $data['slug'] = Str::slug($data['name']);
        $existing = Startup::where('slug', $data['slug'])->where('id', '!=', $startup->id)->exists();
        if ($existing) {
            $data['slug'] = $data['slug'] . '-' . Str::random(4);
        }
        $data['is_featured'] = $request->boolean('is_featured');
        $data['founders'] = $this->buildFoundersFromRequest($request, $startup);
        $first = $this->firstFounderData($data['founders']);
        $data['founder_name'] = $first['name'] ?? $startup->founder_name;
        $data['founder_email'] = $first['email'] ?? $startup->founder_email;
        $data['founder_twitter_url'] = $first['twitter_url'] ?? $startup->founder_twitter_url;
        $data['founder_linkedin_url'] = $first['linkedin_url'] ?? $startup->founder_linkedin_url;
        $data['twitter_url'] = $this->normalizeTwitterInput($data['twitter_url'] ?? null);
        if (($data['status'] ?? $startup->status) === Startup::STATUS_ACTIVE) {
            $data['dormant_at'] = null;
        }
        $startup->update($data);
        $this->processStartupFiles($request, $startup);
        return redirect()->route('admin.startups.index')
            ->with('notify', [['success', 'Startup updated.']]);
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

    public function disable(Startup $startup)
    {
        $startup->update(['status' => Startup::STATUS_DISABLED]);
        return response()->json(['status' => 'success', 'message' => 'Startup disabled.']);
    }

    public function activate(Startup $startup)
    {
        $startup->update([
            'status' => Startup::STATUS_ACTIVE,
            'dormant_at' => null,
        ]);
        return response()->json(['status' => 'success', 'message' => 'Startup activated.']);
    }

    public function ban(Startup $startup)
    {
        $startup->update(['status' => Startup::STATUS_BANNED]);
        return response()->json(['status' => 'success', 'message' => 'Startup banned.']);
    }

    public function unban(Startup $startup)
    {
        $startup->update(['status' => Startup::STATUS_ACTIVE]);
        return response()->json(['status' => 'success', 'message' => 'Startup unbanned.']);
    }

    public function toggleFeatured(Startup $startup)
    {
        $startup->update(['is_featured' => !$startup->is_featured]);
        $label = $startup->is_featured ? 'Featured' : 'Unfeatured';
        return response()->json(['status' => 'success', 'message' => "Startup {$label}.", 'is_featured' => $startup->is_featured]);
    }

    public function toggleFeaturedOnHero(Startup $startup): RedirectResponse
    {
        $startup->update(['featured_on_hero' => ! $startup->featured_on_hero]);
        $label = $startup->featured_on_hero ? 'featured on hero' : 'removed from hero';
        return redirect()->route('admin.startups.index')
            ->with('notify', [['success', $startup->name . ' ' . $label . '.']]);
    }

    public function destroy(Startup $startup): JsonResponse
    {
        if (!$startup->isDisabled()) {
            return response()->json(['status' => 'error', 'message' => 'Only disabled startups can be deleted.'], 422);
        }
        StartupUpvote::where('startup_id', $startup->id)->delete();
        $startup->delete();
        return response()->json(['status' => 'success', 'message' => 'Startup deleted.']);
    }

    private function formResponse(string $title, Startup $startup)
    {
        $categories = Category::orderBy('sort_order')->get();
        $content = view('eden.startups.form', ['startup' => $startup, 'categories' => $categories])->render();
        return response()->view('eden.layout-dashboard', [
            'title' => $title,
            'sidebar' => 'admin',
            'activeNav' => 'startups',
            'dashboardLogo' => (function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden') . ' Admin',
            'dashboardTopbar' => '<button type="button" class="dash-account" title="Property">All startups</button>',
            'searchPlaceholder' => "Try searching 'startups by category'",
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

    private function validationRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'exists:categories,name'],
            'website' => ['nullable', 'string', 'max:500', 'url'],
            'location' => ['nullable', 'string', 'max:255'],
            'founder_email' => ['nullable', 'email', 'max:255'],
            'founder_twitter_url' => ['nullable', 'string', 'max:255'],
            'founder_linkedin_url' => ['nullable', 'string', 'max:500', 'url'],
            'launch_date' => ['nullable', 'date'],
            'twitter_url' => ['nullable', 'string', 'max:255'],
            'linkedin_url' => ['nullable', 'string', 'max:500', 'url'],
            'status' => ['nullable', 'in:active,disabled,banned,dormant'],
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
            'views' => ['nullable', 'integer', 'min:0'],
            'clicks' => ['nullable', 'integer', 'min:0'],
        ];
    }

    private function validationMessages(): array
    {
        return [];
    }
}
