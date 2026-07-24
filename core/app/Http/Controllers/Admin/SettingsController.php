<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Models\NotificationTemplate;
use App\Constants\Status;
use App\Services\RobotsTxtService;
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
            'head_subtitle' => 'The app directory for discoverability and growth.',
            'what_we_do_title' => 'What we do',
            'what_we_do_body' => $siteName . ' is a curated directory of startups. We help founders get discovered by investors, customers, and partners—and help everyone else find the next wave of innovation.',
            'for_founders_title' => 'For founders',
            'for_founders_body' => 'Submit your app once. It appears in search, categories, and—if you\'re launching today—on the Launching today page. No paywall for a basic listing.',
            'for_visitors_title' => 'For visitors',
            'for_visitors_body' => 'Browse by category, search by name or tag, and check the Launching today page for fresh launches. Subscribe to the weekly digest to stay updated.',
            'guidelines_title' => 'Guidelines',
            'guidelines_items' => [
                'Your app must be real and operational (no placeholders).',
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
        $adsenseEnabled = $general ? (bool) ($general->adsense_enabled ?? false) : false;
        $adsenseScript = $general ? (string) ($general->adsense_script ?? '') : '';
        $robotsTxt = $general && isset($general->robots_txt) ? (string) $general->robots_txt : '';
        $recommendedRobotsTxt = RobotsTxtService::recommendedContent();
        $linkedinClientId = $general ? (string) ($general->linkedin_client_id ?? '') : '';
        $linkedinClientSecretSet = $general && ! empty(trim((string) ($general->linkedin_client_secret ?? '')));
        $content = view('eden.settings.index', compact('adsenseEnabled', 'adsenseScript', 'robotsTxt', 'recommendedRobotsTxt', 'linkedinClientId', 'linkedinClientSecretSet'))->render();

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
            return redirect()->route('admin.seo')->with('notify', [['error', 'General settings not found. Run migrations and ensure the general_settings table exists.']]);
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

        return redirect()->route('admin.seo')->with('notify', [['success', 'SEO settings saved.']]);
    }

    public function seo()
    {
        $general = function_exists('gs') ? gs() : null;
        $seo = $general ? (object) [
            'meta_keywords' => $general->meta_keywords ?? '',
            'meta_description' => $general->meta_description ?? '',
            'social_description' => $general->social_description ?? '',
            'seo_image' => $general->seo_image ?? '',
        ] : (object) ['meta_keywords' => '', 'meta_description' => '', 'social_description' => '', 'seo_image' => ''];
        $content = view('eden.settings.seo', compact('seo'))->render();

        return response()->view('eden.layout-dashboard', [
            'title' => 'SEO',
            'sidebar' => 'admin',
            'activeNav' => 'seo',
            'dashboardLogo' => (function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden') . ' Admin',
            'dashboardTopbar' => '',
            'searchPlaceholder' => 'Search…',
            'avatarTitle' => 'Admin',
            'avatarLetter' => 'A',
            'content' => $content,
            'scriptDeps' => '<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>',
            'notifyPartial' => view('partials.notify')->render(),
        ]);
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
            return redirect()->route('admin.about')->with('notify', [['error', 'General settings not found. Run migrations and ensure the general_settings table exists.']]);
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

        return redirect()->route('admin.about')->with('notify', [['success', 'About page content saved.']]);
    }

    public function aboutPage()
    {
        $general = function_exists('gs') ? gs() : null;
        $aboutDefaults = self::aboutPageDefaults();
        $about = $general && is_array($general->about_page ?? null) ? array_merge($aboutDefaults, $general->about_page) : $aboutDefaults;
        $content = view('eden.settings.about', compact('about'))->render();

        return response()->view('eden.layout-dashboard', [
            'title' => 'About page',
            'sidebar' => 'admin',
            'activeNav' => 'about',
            'dashboardLogo' => (function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden') . ' Admin',
            'dashboardTopbar' => '',
            'searchPlaceholder' => 'Search…',
            'avatarTitle' => 'Admin',
            'avatarLetter' => 'A',
            'content' => $content,
            'scriptDeps' => '<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>',
            'notifyPartial' => view('partials.notify')->render(),
        ]);
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

    public function updateLinkedIn(Request $request): RedirectResponse
    {
        $request->validate([
            'linkedin_client_id' => 'nullable|string|max:255',
            'linkedin_client_secret' => 'nullable|string|max:255',
        ]);

        $general = $this->getOrCreateGeneral();
        if (! $general) {
            return redirect()->route('admin.settings.index')->with('notify', [['error', 'General settings not found.']]);
        }

        $general->linkedin_client_id = $request->filled('linkedin_client_id') ? trim($request->linkedin_client_id) : null;
        if ($request->filled('linkedin_client_secret')) {
            $general->linkedin_client_secret = trim($request->linkedin_client_secret);
        }
        $general->save();
        Cache::forget('GeneralSetting');

        return redirect()->route('admin.settings.index')->with('notify', [['success', 'LinkedIn API credentials saved.']]);
    }

    public function updateRobots(Request $request): RedirectResponse
    {
        $request->validate([
            'robots_txt' => 'nullable|string|max:8000',
        ]);

        $general = $this->getOrCreateGeneral();
        if (! $general) {
            return redirect()->route('admin.settings.index')->with('notify', [['error', 'General settings not found. Run migrations and ensure the general_settings table exists.']]);
        }

        $content = $request->filled('robots_txt') ? trim($request->robots_txt) : null;
        if ($content !== null && $content !== '') {
            $general->robots_txt = $content;
            $general->save();
            try {
                app(RobotsTxtService::class)->writeToPublic($content);
            } catch (\Throwable $e) {
                return redirect()->route('admin.settings.index')->with('notify', [['error', 'Saved to database but could not write robots.txt file: ' . $e->getMessage()]]);
            }
        } else {
            $general->robots_txt = null;
            $general->save();
            if (file_exists(public_path('robots.txt'))) {
                @unlink(public_path('robots.txt'));
            }
        }
        Cache::forget('GeneralSetting');

        return redirect()->route('admin.settings.index')->with('notify', [['success', 'Robots.txt saved and file updated.']]);
    }

    public function email()
    {
        $general = function_exists('gs') ? gs() : null;
        $mailConfig = $general ? ($general->mail_config ?? null) : null;

        $emailFromName = $general ? (string) ($general->email_from_name ?? '') : '';
        $emailFrom = $general ? (string) ($general->email_from ?? '') : '';
        $emailTemplate = $general ? (string) ($general->email_template ?? '') : '';

        $emailNotificationsEnabled = $general ? (bool) ($general->en ?? false) : false;
        $welcomeEmailEnabled = $general ? (bool) ($general->welcome_email_enable ?? false) : false;
        $verificationRequired = $general ? (bool) ($general->ev ?? false) : false;

        // Safely query notification templates - handle case where table may not exist yet
        try {
            $welcomeTemplate = NotificationTemplate::where('act', 'WELCOME_EMAIL')->first();
            $verificationTemplate = NotificationTemplate::where('act', 'EVER_CODE')->first();
        } catch (\Exception $e) {
            $welcomeTemplate = null;
            $verificationTemplate = null;
        }

        $content = view('eden.settings.email', compact(
            'mailConfig',
            'emailFromName',
            'emailFrom',
            'emailTemplate',
            'emailNotificationsEnabled',
            'welcomeEmailEnabled',
            'verificationRequired',
            'welcomeTemplate',
            'verificationTemplate'
        ))->render();

        return response()->view('eden.layout-dashboard', [
            'title' => 'Email settings',
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

    public function updateEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email_method' => 'required|in:php,smtp,sendgrid,mailjet',
            'host' => 'required_if:email_method,smtp',
            'port' => 'required_if:email_method,smtp',
            'username' => 'required_if:email_method,smtp',
            'password' => 'required_if:email_method,smtp',
            'enc' => 'required_if:email_method,smtp',
            'appkey' => 'required_if:email_method,sendgrid',
            'public_key' => 'required_if:email_method,mailjet',
            'secret_key' => 'required_if:email_method,mailjet',
            'email_from_name' => 'required|string|max:191',
            'email_from' => 'required|email|string|max:191',
            'email_template' => 'required|string',
            'welcome_subject' => 'required|string|max:255',
            'welcome_body' => 'required|string',
            'verification_subject' => 'required|string|max:255',
            'verification_body' => 'required|string',
        ], [
            'host.required_if' => 'The :attribute is required for SMTP configuration',
            'port.required_if' => 'The :attribute is required for SMTP configuration',
            'username.required_if' => 'The :attribute is required for SMTP configuration',
            'password.required_if' => 'The :attribute is required for SMTP configuration',
            'enc.required_if' => 'The :attribute is required for SMTP configuration',
            'appkey.required_if' => 'The :attribute is required for SendGrid configuration',
            'public_key.required_if' => 'The :attribute is required for Mailjet configuration',
            'secret_key.required_if' => 'The :attribute is required for Mailjet configuration',
        ]);

        $general = $this->getOrCreateGeneral();
        if (! $general) {
            return redirect()->route('admin.settings.index')->with('notify', [['error', 'General settings not found. Run migrations and ensure the general_settings table exists.']]);
        }

        if ($request->email_method === 'php') {
            $data = ['name' => 'php'];
        } elseif ($request->email_method === 'smtp') {
            $request->merge(['name' => 'smtp']);
            $data = $request->only('name', 'host', 'port', 'enc', 'username', 'password', 'driver');
        } elseif ($request->email_method === 'sendgrid') {
            $request->merge(['name' => 'sendgrid']);
            $data = $request->only('name', 'appkey');
        } else { // mailjet
            $request->merge(['name' => 'mailjet']);
            $data = $request->only('name', 'public_key', 'secret_key');
        }

        $general->mail_config = $data;
        $general->email_from = $request->input('email_from');
        $general->email_from_name = $request->input('email_from_name');
        $general->email_template = $request->input('email_template');

        $general->en = $request->boolean('email_notifications_enabled') ? Status::ENABLE : Status::DISABLE;
        $general->welcome_email_enable = $request->boolean('welcome_email_enabled') ? Status::ENABLE : Status::DISABLE;
        $general->ev = $request->boolean('verification_required') ? Status::ENABLE : Status::DISABLE;

        $general->save();
        Cache::forget('GeneralSetting');

        $welcomeTemplate = NotificationTemplate::where('act', 'WELCOME_EMAIL')->first();
        if ($welcomeTemplate) {
            $welcomeTemplate->subject = $request->input('welcome_subject');
            $welcomeTemplate->email_body = $request->input('welcome_body');
            $welcomeTemplate->email_status = $request->boolean('welcome_email_enabled') ? Status::ENABLE : Status::DISABLE;
            $welcomeTemplate->save();
        }

        $verificationTemplate = NotificationTemplate::where('act', 'EVER_CODE')->first();
        if ($verificationTemplate) {
            $verificationTemplate->subject = $request->input('verification_subject');
            $verificationTemplate->email_body = $request->input('verification_body');
            $verificationTemplate->email_status = $request->boolean('verification_required') ? Status::ENABLE : Status::DISABLE;
            $verificationTemplate->save();
        }

        return redirect()->route('admin.settings.email')->with('notify', [['success', 'Email settings saved.']]);
    }
}
