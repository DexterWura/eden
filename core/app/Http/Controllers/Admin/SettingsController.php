<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    private function getOrCreateGeneral(): ?GeneralSetting
    {
        $general = function_exists('gs') ? gs() : null;
        if ($general instanceof GeneralSetting && $general->exists) {
            return $general;
        }
        $general = GeneralSetting::first();
        if ($general) {
            return $general;
        }
        try {
            $general = GeneralSetting::create(['site_name' => 'Eden']);
            Cache::forget('GeneralSetting');
            return $general;
        } catch (\Throwable $e) {
            return null;
        }
    }
    public static function aboutPageDefaults(): array
    {
        $siteName = function_exists('gs') && gs('site_name') ? gs('site_name') : 'Eden';
        return [
            'head_title' => 'About ' . $siteName,
            'head_subtitle' => 'The startup directory for discoverability and growth.',
            'what_we_do_title' => 'What we do',
            'what_we_do_body' => $siteName . ' is a curated directory of startups. We help founders get discovered by investors, customers, and partners—and help everyone else find the next wave of innovation.',
            'for_founders_title' => 'For founders',
            'for_founders_body' => 'Submit your startup once. It appears in search, categories, and—if you\'re launching today—on the Launching today page. No paywall for a basic listing.',
            'for_visitors_title' => 'For visitors',
            'for_visitors_body' => 'Browse by category, search by name or tag, and check the Launching today page for fresh launches. Subscribe to the weekly digest to stay updated.',
            'guidelines_title' => 'Guidelines',
            'guidelines_items' => [
                'Your startup must be real and operational (no placeholders).',
                'Provide a clear description and link.',
                'One listing per startup. Updates are free.',
            ],
            'cta_title' => 'Ready to list your startup?',
            'cta_subtitle' => 'Submit in under 2 minutes.',
        ];
    }

    public function index()
    {
        $general = function_exists('gs') ? gs() : null;
        $seo = $general ? (object) [
            'meta_keywords' => $general->meta_keywords ?? '',
            'meta_description' => $general->meta_description ?? '',
            'social_description' => $general->social_description ?? '',
            'seo_image' => $general->seo_image ?? '',
        ] : (object) ['meta_keywords' => '', 'meta_description' => '', 'social_description' => '', 'seo_image' => ''];
        $aboutDefaults = self::aboutPageDefaults();
        $about = $general && is_array($general->about_page ?? null) ? array_merge($aboutDefaults, $general->about_page) : $aboutDefaults;
        $adsenseEnabled = $general ? (bool) ($general->adsense_enabled ?? false) : false;
        $adsenseScript = $general ? (string) ($general->adsense_script ?? '') : '';
        $content = view('eden.settings.index', compact('seo', 'about', 'adsenseEnabled', 'adsenseScript'))->render();

        return response()->view('eden.layout-dashboard', [
            'title' => 'Settings',
            'sidebar' => 'admin',
            'activeNav' => 'settings',
            'dashboardLogo' => (function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden') . ' Admin',
            'dashboardTopbar' => '',
            'searchPlaceholder' => "Search…",
            'avatarTitle' => 'Admin',
            'avatarLetter' => 'A',
            'content' => $content,
            'scriptDeps' => '<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>',
            'notifyPartial' => view('partials.notify')->render(),
        ]);
    }

    public function updateSeo(Request $request): RedirectResponse
    {
        $request->validate([
            'meta_keywords' => 'nullable|string|max:2000',
            'meta_description' => 'nullable|string|max:1000',
            'social_description' => 'nullable|string|max:1000',
            'seo_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $general = $this->getOrCreateGeneral();
        if (! $general) {
            return redirect()->route('admin.settings.index')->with('notify', [['error', 'General settings not found. Run migrations and ensure the general_settings table exists.']]);
        }

        $general->meta_keywords = $request->filled('meta_keywords') ? $request->meta_keywords : null;
        $general->meta_description = $request->filled('meta_description') ? $request->meta_description : null;
        $general->social_description = $request->filled('social_description') ? $request->social_description : null;

        if ($request->hasFile('seo_image')) {
            $dir = public_path('images/seo');
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            $name = 'og-' . time() . '.' . $request->seo_image->getClientOriginalExtension();
            $request->seo_image->move($dir, $name);
            $general->seo_image = 'images/seo/' . $name;
        }

        $general->save();
        Cache::forget('GeneralSetting');

        return redirect()->route('admin.settings.index')->with('notify', [['success', 'SEO settings saved.']]);
    }

    public function updateAbout(Request $request): RedirectResponse
    {
        $request->validate([
            'head_title' => 'nullable|string|max:255',
            'head_subtitle' => 'nullable|string|max:500',
            'what_we_do_title' => 'nullable|string|max:255',
            'what_we_do_body' => 'nullable|string|max:5000',
            'for_founders_title' => 'nullable|string|max:255',
            'for_founders_body' => 'nullable|string|max:5000',
            'for_visitors_title' => 'nullable|string|max:255',
            'for_visitors_body' => 'nullable|string|max:5000',
            'guidelines_title' => 'nullable|string|max:255',
            'guidelines_items' => 'nullable|string|max:5000',
            'cta_title' => 'nullable|string|max:255',
            'cta_subtitle' => 'nullable|string|max:500',
        ]);

        $general = $this->getOrCreateGeneral();
        if (! $general) {
            return redirect()->route('admin.settings.index')->with('notify', [['error', 'General settings not found. Run migrations and ensure the general_settings table exists.']]);
        }

        $items = $request->input('guidelines_items', '');
        $guidelinesItems = array_values(array_filter(array_map('trim', explode("\n", $items))));

        $general->about_page = [
            'head_title' => $request->input('head_title'),
            'head_subtitle' => $request->input('head_subtitle'),
            'what_we_do_title' => $request->input('what_we_do_title'),
            'what_we_do_body' => $request->input('what_we_do_body'),
            'for_founders_title' => $request->input('for_founders_title'),
            'for_founders_body' => $request->input('for_founders_body'),
            'for_visitors_title' => $request->input('for_visitors_title'),
            'for_visitors_body' => $request->input('for_visitors_body'),
            'guidelines_title' => $request->input('guidelines_title'),
            'guidelines_items' => $guidelinesItems,
            'cta_title' => $request->input('cta_title'),
            'cta_subtitle' => $request->input('cta_subtitle'),
        ];
        $general->save();
        Cache::forget('GeneralSetting');

        return redirect()->route('admin.settings.index')->with('notify', [['success', 'About page content saved.']]);
    }

    public function updateAdsense(Request $request): RedirectResponse
    {
        $request->validate([
            'adsense_enabled' => 'nullable',
            'adsense_script' => 'nullable|string|max:10000',
        ]);

        $general = $this->getOrCreateGeneral();
        if (! $general) {
            return redirect()->route('admin.settings.index')->with('notify', [['error', 'General settings not found. Run migrations and ensure the general_settings table exists.']]);
        }

        $general->adsense_enabled = $request->boolean('adsense_enabled');
        $general->adsense_script = $request->filled('adsense_script') ? $request->adsense_script : null;
        $general->save();
        Cache::forget('GeneralSetting');

        return redirect()->route('admin.settings.index')->with('notify', [['success', 'AdSense settings saved.']]);
    }
}
