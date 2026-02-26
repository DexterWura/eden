<?php

namespace App\Http\Controllers\Founder;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Startup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StartupController extends Controller
{
    public function index()
    {
        $startups = auth()->user()->startups()->orderByDesc('updated_at')->get();

        $content = view('eden.founder.startups-index', ['startups' => $startups])->render();

        return $this->layoutResponse('My startups', 'startups', $content);
    }

    public function create()
    {
        $startup = new Startup(['status' => Startup::STATUS_ACTIVE]);
        $categories = Category::orderBy('sort_order')->get();
        $content = view('eden.founder.startups-form', ['startup' => $startup, 'categories' => $categories])->render();
        return $this->layoutResponse('Add startup', 'startups', $content, true);
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules(), []);
        if ($validator->fails()) {
            return redirect()->route('founder.startups.create')->withErrors($validator)->withInput();
        }
        $data = $validator->validated();
        unset($data['logo'], $data['founders_names'], $data['founders_emails'], $data['founders_twitter_urls'], $data['founders_linkedin_urls'], $data['founders_photos'], $data['product_images']);
        $data['user_id'] = auth()->id();
        $data['slug'] = Str::slug($data['name']);
        if (Startup::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $data['slug'] . '-' . Str::random(4);
        }
        $data['status'] = Startup::STATUS_ACTIVE;
        $data['founders'] = $this->buildFoundersFromRequest($request);
        $first = $this->firstFounderData($data['founders']);
        $data['founder_name'] = $first['name'] ?? auth()->user()->name;
        $data['founder_email'] = $first['email'] ?? auth()->user()->email;
        $data['founder_twitter_url'] = $first['twitter_url'] ?? null;
        $data['founder_linkedin_url'] = $first['linkedin_url'] ?? null;
        $data['twitter_url'] = $this->normalizeTwitterInput($data['twitter_url'] ?? null);
        $startup = Startup::create($data);
        $this->processStartupFiles($request, $startup);
        return redirect()->route('founder.startups.index')->with('notify', [['success', 'Startup added.']]);
    }

    public function edit(Startup $startup)
    {
        $this->authorizeStartup($startup);
        $categories = Category::orderBy('sort_order')->get();
        $content = view('eden.founder.startups-form', ['startup' => $startup, 'categories' => $categories])->render();
        return $this->layoutResponse('Edit startup', 'startups', $content, true);
    }

    public function update(Request $request, Startup $startup): RedirectResponse
    {
        $this->authorizeStartup($startup);
        $validator = Validator::make($request->all(), $this->rules(), []);
        if ($validator->fails()) {
            return redirect()->route('founder.startups.edit', $startup)->withErrors($validator)->withInput();
        }
        $data = $validator->validated();
        $data['slug'] = Str::slug($data['name']);
        if (Startup::where('slug', $data['slug'])->where('id', '!=', $startup->id)->exists()) {
            $data['slug'] = $data['slug'] . '-' . Str::random(4);
        }
        unset($data['logo'], $data['founders_names'], $data['founders_emails'], $data['founders_twitter_urls'], $data['founders_linkedin_urls'], $data['founders_photos'], $data['product_images']);
        $data['founders'] = $this->buildFoundersFromRequest($request, $startup);
        $first = $this->firstFounderData($data['founders']);
        $data['founder_name'] = $first['name'] ?? $startup->founder_name;
        $data['founder_email'] = $first['email'] ?? $startup->founder_email;
        $data['founder_twitter_url'] = $first['twitter_url'] ?? $startup->founder_twitter_url;
        $data['founder_linkedin_url'] = $first['linkedin_url'] ?? $startup->founder_linkedin_url;
        $data['twitter_url'] = $this->normalizeTwitterInput($data['twitter_url'] ?? null);
        $startup->update($data);
        $this->processStartupFiles($request, $startup);
        return redirect()->route('founder.startups.index')->with('notify', [['success', 'Startup updated.']]);
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
        if ($startup->user_id !== auth()->id()) {
            abort(403, 'Not your startup.');
        }
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

    private function rules(): array
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
        ];
    }
}
