<?php

namespace App\Http\Controllers\Eden;

use App\Http\Controllers\Admin\SettingsController;
use App\Models\Category;
use App\Models\ContactSubmission;
use App\Models\Startup;
use App\Models\Subscriber;
use App\Models\User;
use App\Rules\SensibleDisplayName;
use App\Rules\SensiblePersonName;
use App\Rules\SensibleShortText;
use App\Services\StartupService;
use App\Support\Seo\EdenSeo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class PageController extends EdenController
{
    public function __construct(
        private StartupService $startupService
    ) {}

    public function about()
    {
        $general = function_exists('gs') ? gs() : null;
        $aboutDefaults = SettingsController::aboutPageDefaults();
        $about = $general && is_array($general->about_page ?? null)
            ? array_merge($aboutDefaults, $general->about_page)
            : $aboutDefaults;
        return $this->page('about', 'About', null, ['about' => $about], EdenSeo::forStaticPath('/about'));
    }

    public function privacy()
    {
        return $this->page('privacy', 'Privacy Policy', null, [], EdenSeo::forStaticPath('/privacy'));
    }

    public function terms()
    {
        return $this->page('terms', 'Terms of Service', null, [], EdenSeo::forStaticPath('/terms'));
    }

    public function contact()
    {
        $contactPrefill = [
            'subject' => request()->query('subject'),
            'message' => request()->query('message'),
            'startup' => request()->query('startup'),
        ];

        return $this->page('contact', 'Contact', null, [
            'contactPrefill' => $contactPrefill,
        ], EdenSeo::forStaticPath('/contact'));
    }

    public function contactStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', new SensiblePersonName()],
            'email' => 'required|email',
            'subject' => ['nullable', 'string', 'max:64', new SensibleShortText(64)],
            'message' => ['required', 'string', 'max:10000', new SensibleShortText(10000)],
        ]);
        ContactSubmission::create($validated);
        return redirect()->to(url('/contact'))->with('success', __('Your message has been sent. We\'ll get back to you soon.'));
    }

    public function submit()
    {
        $categories = Category::orderBy('sort_order')->get();
        return $this->page('submit', 'Submit your startup', null, ['categories' => $categories], EdenSeo::forStaticPath('/submit'));
    }

    public function submitStore(Request $request): RedirectResponse
    {
        $startupRules = [
            'name' => ['required', 'string', 'max:120', new SensibleDisplayName(), function ($attr, $value, $fail) {
                if ($value && Startup::listingNameExistsForAnother($value)) {
                    $fail(__('A listing with this name already exists.'));
                }
            }],
            'tagline' => ['required', 'string', 'min:12', 'max:255', new SensibleShortText(255)],
            'description' => ['required', 'string', 'min:250', 'max:10000'],
            'problem_solved' => ['required', 'string', 'min:80', 'max:3000'],
            'target_customer' => ['required', 'string', 'min:40', 'max:1500'],
            'key_features' => ['required', 'array', 'min:3', 'max:8'],
            'key_features.*' => ['required', 'string', 'min:5', 'max:180', new SensibleShortText(180)],
            'pricing_model' => ['nullable', 'string', 'max:120', new SensibleShortText(120)],
            'markets_served' => ['nullable', 'string', 'max:500', new SensibleShortText(500)],
            'traction' => ['nullable', 'string', 'max:3000'],
            'founder_story' => ['nullable', 'string', 'max:5000'],
            'category' => 'required|string|exists:categories,name',
            'website' => ['nullable', 'url', 'max:500', function ($attr, $value, $fail) {
                $host = strtolower((string) parse_url((string) $value, PHP_URL_HOST));
                if ($host === '' || str_ends_with($host, '.example') || $host === 'example.com') {
                    $fail('Enter the real, public website for this startup.');
                    return;
                }
                if ($value && Startup::websiteExistsForAnother($value)) {
                    $fail('A startup with this website link already exists.');
                }
            }],
            'location' => ['nullable', 'string', 'max:255', new SensibleShortText(255)],
            'founder_names' => 'nullable|array',
            'founder_names.*' => ['nullable', 'string', 'max:80', new SensiblePersonName()],
            'launch_today' => 'nullable|in:today,1,yes',
            'logo' => 'nullable|image|mimes:jpeg,png,gif,webp|max:2048',
            'product_images' => 'nullable|array',
            'product_images.*' => 'nullable|image|mimes:jpeg,png,gif,webp|max:2048',
        ];

        if (!auth()->check()) {
            $authMode = $request->input('auth_mode', 'register');

            if ($authMode === 'login') {
                $startupRules['login_email'] = 'required|email';
                $startupRules['login_password'] = 'required|string';
            } else {
                $startupRules['auth_name'] = ['required', 'string', 'min:2', 'max:80', new SensiblePersonName()];
                $startupRules['auth_email'] = 'required|string|email|max:255|unique:users,email';
                $startupRules['auth_password'] = ['required', 'confirmed', Password::min(8)];
            }
        }

        $validated = $request->validate($startupRules);

        if (!auth()->check()) {
            $authMode = $request->input('auth_mode', 'register');

            if ($authMode === 'login') {
                if (!Auth::guard('web')->attempt(['email' => $validated['login_email'], 'password' => $validated['login_password']])) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['login_email' => __('The provided credentials do not match our records.')]);
                }
                $request->session()->regenerate();
            } else {
                $user = new User();
                $user->name = $validated['auth_name'];
                $user->email = $validated['auth_email'];
                $user->password = Hash::make($validated['auth_password']);
                $user->save();
                Auth::guard('web')->login($user);
                $request->session()->regenerate();
            }
        }

        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $n = 0;
        while (Startup::where('slug', $slug)->exists()) {
            $n++;
            $slug = $baseSlug . '-' . $n;
        }

        $founderNames = array_values(array_filter(array_map('trim', $validated['founder_names'] ?? [])));
        $founders = array_map(fn (string $name) => [
            'name' => $name,
            'photo_url' => null,
            'email' => null,
            'twitter_url' => null,
            'linkedin_url' => null,
        ], $founderNames);
        $founderName = $founders[0]['name'] ?? null;

        $startup = new Startup();
        $startup->name = $validated['name'];
        $startup->slug = $slug;
        $startup->tagline = $validated['tagline'] ?? null;
        $startup->description = $validated['description'] ?? null;
        $startup->problem_solved = $validated['problem_solved'] ?? null;
        $startup->target_customer = $validated['target_customer'] ?? null;
        $startup->key_features = array_values($validated['key_features'] ?? []);
        $startup->pricing_model = $validated['pricing_model'] ?? null;
        $startup->markets_served = $validated['markets_served'] ?? null;
        $startup->traction = $validated['traction'] ?? null;
        $startup->founder_story = $validated['founder_story'] ?? null;
        $startup->category = $validated['category'] ?: null;
        $startup->website = $validated['website'] ?? null;
        $startup->location = $validated['location'] ?? null;
        $startup->founder_name = $founderName;
        $startup->founders = $founders;
        $startup->launch_date = (!empty($validated['launch_today'])) ? now() : null;
        $startup->is_featured = false;
        $startup->upvotes = 0;
        $startup->status = Startup::STATUS_PENDING;
        $startup->user_id = auth()->id();
        $startup->content_quality_version = 1;
        $startup->save();

        $baseDir = public_path('images/startups/' . $startup->id);
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            @mkdir($baseDir, 0755, true);
            $ext = allowed_image_extension($request->file('logo'));
            $request->file('logo')->move($baseDir, 'logo.' . $ext);
            $startup->update(['logo_path' => 'images/startups/' . $startup->id . '/logo.' . $ext]);
        }
        $productFiles = $request->file('product_images', []);
        if (!empty($productFiles)) {
            @mkdir($baseDir, 0755, true);
            $productDir = $baseDir . '/products';
            @mkdir($productDir, 0755, true);
            $paths = [];
            foreach ($productFiles as $file) {
                if ($file->isValid()) {
                    $ext = allowed_image_extension($file);
                    $filename = 'p-' . uniqid() . '.' . $ext;
                    $file->move($productDir, $filename);
                    $paths[] = 'images/startups/' . $startup->id . '/products/' . $filename;
                }
            }
            if (!empty($paths)) {
                $startup->update(['product_images' => $paths]);
            }
        }

        return redirect()->to(url('/startup/' . $startup->slug))->with('success', __('Your startup has been submitted and is pending review by our team. It will go live once approved.'));
    }

    public function categories()
    {
        $allCategories = Category::orderBy('sort_order')->orderBy('name')->get();
        $countsByCategory = $this->startupService->getCategoriesWithCounts()->keyBy('category');
        foreach ($allCategories as $cat) {
            $cat->count = $countsByCategory->has($cat->name) ? (int) $countsByCategory->get($cat->name)->count : 0;
        }
        return $this->page('categories', 'Categories', null, ['categories' => $allCategories], EdenSeo::forStaticPath('/categories'));
    }

    public function subscribe(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email|unique:subscribers,email'], [
            'email.unique' => __('You have already subscribed.'),
        ]);
        Subscriber::create(['email' => $request->email]);
        return redirect()->back()->with('success', __('Thanks for subscribing.'));
    }
}
