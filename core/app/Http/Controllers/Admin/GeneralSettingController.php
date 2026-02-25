<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Frontend;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use App\Traits\MasksSensitiveData;

class GeneralSettingController extends Controller
{
    use MasksSensitiveData;
    public function systemSetting()
    {
        $pageTitle = 'System Settings';
        $settings  = json_decode(file_get_contents(resource_path('views/admin/setting/settings.json')));
        return view('admin.setting.system', compact('pageTitle', 'settings'));
    }
    public function general()
    {
        $pageTitle       = 'General Setting';
        $timezones       = timezone_identifiers_list();
        $currentTimezone = array_search(config('app.timezone'), $timezones);
        return view('admin.setting.general', compact('pageTitle', 'timezones', 'currentTimezone'));
    }

    public function generalUpdate(Request $request)
    {
        $request->validate([
            'site_name'       => 'required|string|max:40',
            'cur_text'        => 'required|string|max:40',
            'cur_sym'         => 'required|string|max:40',
            'base_color'      => 'nullable|regex:/^[a-f0-9]{6}$/i',
            'secondary_color' => 'nullable|regex:/^[a-f0-9]{6}$/i',
            'timezone'        => 'required|integer',
            'currency_format' => 'required|in:1,2,3',
            'paginate_number' => 'required|integer'
        ]);

        $timezones = timezone_identifiers_list();
        $timezone  = @$timezones[$request->timezone] ?? 'UTC';

        $general                  = gs();
        $general->site_name       = $request->site_name;
        $general->cur_text        = $request->cur_text;
        $general->cur_sym         = $request->cur_sym;
        $general->paginate_number = $request->paginate_number;
        $general->base_color      = str_replace('#', '', $request->base_color);
        $general->secondary_color = str_replace('#', '', $request->secondary_color);
        $general->currency_format = $request->currency_format;
        $general->save();

        $timezoneFile = config_path('timezone.php');
        $content      = '<?php $timezone = "' . $timezone . '" ?>';
        file_put_contents($timezoneFile, $content);
        $notify[] = ['success', 'General setting updated successfully'];
        return back()->withNotify($notify);
    }

    public function affiliateSetting()
    {
        $pageTitle = 'Affiliate Settings';
        return view('admin.setting.affiliate', compact('pageTitle'));
    }

    public function affiliateSettingUpdate(Request $request)
    {
        $request->validate([
            'affiliate_signup_amount' => 'required|numeric|min:0',
        ]);
        $general = gs();
        $general->affiliate_enable = ($request->has('affiliate_enable') && $request->affiliate_enable) ? 1 : 0;
        $general->affiliate_signup_amount = (float) $request->affiliate_signup_amount;
        $general->save();
        $notify[] = ['success', 'Affiliate settings updated successfully'];
        return back()->withNotify($notify);
    }

    public function systemConfiguration()
    {
        $pageTitle = 'System Configuration';
        return view('admin.setting.configuration', compact('pageTitle'));
    }


    public function systemConfigurationSubmit(Request $request)
    {
        $general                  = gs();
        $general->kv              = $request->kv ? Status::ENABLE : Status::DISABLE;
        $general->ev              = $request->ev ? Status::ENABLE : Status::DISABLE;
        $general->en              = $request->en ? Status::ENABLE : Status::DISABLE;
        $general->sv              = $request->sv ? Status::ENABLE : Status::DISABLE;
        $general->sn              = $request->sn ? Status::ENABLE : Status::DISABLE;
        $general->pn              = $request->pn ? Status::ENABLE : Status::DISABLE;
        $general->force_ssl       = $request->force_ssl ? Status::ENABLE : Status::DISABLE;
        $general->secure_password = $request->secure_password ? Status::ENABLE : Status::DISABLE;
        $general->registration    = $request->registration ? Status::ENABLE : Status::DISABLE;
        $general->welcome_email_enable = $request->welcome_email_enable ? Status::ENABLE : Status::DISABLE;
        $general->agree           = $request->agree ? Status::ENABLE : Status::DISABLE;
        $general->multi_language  = $request->multi_language ? Status::ENABLE : Status::DISABLE;
        $general->save();
        $notify[] = ['success', 'System configuration updated successfully'];
        return back()->withNotify($notify);
    }


    public function logoIcon()
    {
        $pageTitle = 'Logo & Favicon';
        return view('admin.setting.logo_icon', compact('pageTitle'));
    }

    public function logoIconUpdate(Request $request)
    {
        $request->validate([
            'logo'    => ['image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
            'favicon' => ['image', new FileTypeValidate(['png'])],
        ]);
        $path = getFilePath('logo_icon');
        if ($request->hasFile('logo')) {
            try {
                fileUploader($request->logo, $path, filename: 'logo.png');
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload the logo'];
                return back()->withNotify($notify);
            }
        }

        if ($request->hasFile('favicon')) {
            try {
                fileUploader($request->favicon, $path, filename: 'favicon.png');
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload the favicon'];
                return back()->withNotify($notify);
            }
        }
        $notify[] = ['success', 'Logo & favicon updated successfully'];
        return back()->withNotify($notify);
    }

    public function customCss()
    {
        $pageTitle   = 'Custom CSS';
        $file        = activeTemplate(true) . 'css/custom.css';
        $fileContent = @file_get_contents($file);
        return view('admin.setting.custom_css', compact('pageTitle', 'fileContent'));
    }

    public function adsenseSetting()
    {
        $pageTitle = 'Google AdSense';
        return view('admin.setting.adsense', compact('pageTitle'));
    }

    public function adsenseSettingSubmit(Request $request)
    {
        $request->validate([
            'google_adsense_script' => 'nullable|string|max:65535',
        ]);
        $general = gs();
        $general->google_adsense_enable = ($request->has('google_adsense_enable') && $request->google_adsense_enable) ? Status::ENABLE : Status::DISABLE;
        $general->google_adsense_script = $request->google_adsense_script ?: null;
        $general->save();
        $notify[] = ['success', 'Google AdSense settings updated successfully'];
        return back()->withNotify($notify);
    }

    public function sitemap()
    {
        $pageTitle = 'Sitemap XML';
        
        // Generate dynamic sitemap
        $sitemapController = new \App\Http\Controllers\SitemapController();
        $dynamicSitemap = $sitemapController->generateXml();
        
        // Check if static file exists
        $file = 'sitemap.xml';
        $staticFileContent = @file_get_contents($file);
        
        // Use dynamic sitemap as default, fallback to static if dynamic is empty
        $fileContent = !empty($dynamicSitemap) ? $dynamicSitemap : $staticFileContent;
        
        // Get statistics
        $stats = $this->getSitemapStats($dynamicSitemap);
        
        return view('admin.setting.sitemap', compact('pageTitle', 'fileContent', 'dynamicSitemap', 'stats'));
    }

    public function sitemapSubmit(Request $request)
    {
        $request->validate([
            'sitemap' => 'required|string',
        ]);
        
        $file = 'sitemap.xml';
        if (!file_exists($file)) {
            fopen($file, "w");
        }
        file_put_contents($file, $request->sitemap);
        $notify[] = ['success', 'Sitemap updated successfully'];
        return back()->withNotify($notify);
    }

    public function regenerateSitemap()
    {
        try {
            $sitemapController = new \App\Http\Controllers\SitemapController();
            $dynamicSitemap = $sitemapController->generateXml();
            
            // Optionally save to file
            $file = 'sitemap.xml';
            file_put_contents($file, $dynamicSitemap);
            
            $notify[] = ['success', 'Sitemap regenerated successfully'];
        } catch (\Exception $e) {
            $notify[] = ['error', 'Failed to regenerate sitemap: ' . $e->getMessage()];
        }
        
        return back()->withNotify($notify);
    }

    private function getSitemapStats($xml)
    {
        if (empty($xml)) {
            return [
                'total_urls' => 0,
                'listings' => 0,
                'categories' => 0,
                'blogs' => 0,
                'pages' => 0,
            ];
        }
        
        // Count total URLs
        $totalUrls = substr_count($xml, '<url>');
        
        // Count URLs by pattern matching
        $baseUrl = url('/');
        $listings = preg_match_all('/<loc>' . preg_quote($baseUrl, '/') . '\/marketplace\/listing\/[^<]+<\/loc>/', $xml);
        $categories = preg_match_all('/<loc>' . preg_quote($baseUrl, '/') . '\/marketplace\/category\/[^<]+<\/loc>/', $xml);
        $blogs = preg_match_all('/<loc>' . preg_quote($baseUrl, '/') . '\/blog\/[^<]+<\/loc>/', $xml);
        $pages = preg_match_all('/<loc>' . preg_quote($baseUrl, '/') . '\/pages\/[^<]+<\/loc>/', $xml);
        
        return [
            'total_urls' => $totalUrls,
            'listings' => $listings ?: 0,
            'categories' => $categories ?: 0,
            'blogs' => $blogs ?: 0,
            'pages' => $pages ?: 0,
        ];
    }



    public function robot()
    {
        $pageTitle = 'Robots TXT';
        
        // Generate secure default robots.txt
        $sitemapController = new \App\Http\Controllers\SitemapController();
        $defaultRobots = $sitemapController->generateRobotsTxt();
        
        // Check if static file exists
        $file = base_path('../robots.txt');
        $staticFileContent = @file_get_contents($file);
        
        // Use static file if exists and has content, otherwise use default
        $fileContent = (!empty($staticFileContent)) ? $staticFileContent : $defaultRobots;
        
        return view('admin.setting.robots', compact('pageTitle', 'fileContent', 'defaultRobots'));
    }

    public function robotSubmit(Request $request)
    {
        $request->validate([
            'robots' => 'required|string',
        ]);
        
        $file = base_path('../robots.txt');
        if (!file_exists($file)) {
            fopen($file, "w");
        }
        file_put_contents($file, $request->robots);
        $notify[] = ['success', 'Robots txt updated successfully'];
        return back()->withNotify($notify);
    }

    public function regenerateRobots()
    {
        try {
            $sitemapController = new \App\Http\Controllers\SitemapController();
            $defaultRobots = $sitemapController->generateRobotsTxt();
            
            // Save to file
            $file = base_path('../robots.txt');
            file_put_contents($file, $defaultRobots);
            
            $notify[] = ['success', 'Robots.txt regenerated successfully with secure defaults'];
        } catch (\Exception $e) {
            $notify[] = ['error', 'Failed to regenerate robots.txt: ' . $e->getMessage()];
        }
        
        return back()->withNotify($notify);
    }


    public function customCssSubmit(Request $request)
    {
        $file = activeTemplate(true) . 'css/custom.css';
        if (!file_exists($file)) {
            fopen($file, "w");
        }
        file_put_contents($file, $request->css);
        $notify[] = ['success', 'CSS updated successfully'];
        return back()->withNotify($notify);
    }

    public function maintenanceMode()
    {
        $pageTitle   = 'Maintenance Mode';
        $maintenance = Frontend::where('data_keys', 'maintenance.data')->firstOrFail();
        return view('admin.setting.maintenance', compact('pageTitle', 'maintenance'));
    }

    public function maintenanceModeSubmit(Request $request)
    {
        $request->validate([
            'description' => 'required',
            'image'       => ['nullable', new FileTypeValidate(['jpg', 'jpeg', 'png'])]
        ]);
        $general                   = gs();
        $general->maintenance_mode = $request->status ? Status::ENABLE : Status::DISABLE;
        $general->save();

        $maintenance = Frontend::where('data_keys', 'maintenance.data')->firstOrFail();
        $image       = @$maintenance->data_values->image;
        if ($request->hasFile('image')) {
            try {
                $old   = $image;
                $image = fileUploader($request->image, getFilePath('maintenance'), getFileSize('maintenance'), $old);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload your image'];
                return back()->withNotify($notify);
            }
        }

        $maintenance->data_values = [
            'description' => $request->description,
            'image'       => $image
        ];
        $maintenance->save();

        $notify[] = ['success', 'Maintenance mode updated successfully'];
        return back()->withNotify($notify);
    }

    public function cookie()
    {
        $pageTitle = 'GDPR Cookie';
        $cookie    = Frontend::where('data_keys', 'cookie.data')->firstOrFail();
        return view('admin.setting.cookie', compact('pageTitle', 'cookie'));
    }

    public function cookieSubmit(Request $request)
    {
        $request->validate([
            'short_desc'  => 'required|string|max:255',
            'description' => 'required',
        ]);
        $cookie              = Frontend::where('data_keys', 'cookie.data')->firstOrFail();
        $cookie->data_values = [
            'short_desc'  => $request->short_desc,
            'description' => $request->description,
            'status'      => $request->status ? Status::ENABLE : Status::DISABLE,
        ];
        $cookie->save();
        $notify[] = ['success', 'Cookie policy updated successfully'];
        return back()->withNotify($notify);
    }


    public function socialiteCredentials()
    {
        $pageTitle = 'Social Login Credentials';
        
        // Mask sensitive social login credentials if demo user
        $socialiteCredentials = gs('socialite_credentials');
        if (auth('admin')->user() && auth('admin')->user()->isDemoUser() && $socialiteCredentials) {
            foreach ($socialiteCredentials as $provider => $credential) {
                if (is_object($credential)) {
                    if (isset($credential->client_id)) {
                        $socialiteCredentials->$provider->client_id = '{protected in demo mode}';
                    }
                    if (isset($credential->client_secret)) {
                        $socialiteCredentials->$provider->client_secret = '{protected in demo mode}';
                    }
                }
            }
        }
        
        return view('admin.setting.social_credential', compact('pageTitle'));
    }

    public function updateSocialiteCredentialStatus($key)
    {
        $general     = gs();
        $credentials = $general->socialite_credentials;
        try {
            $credentials->$key->status = $credentials->$key->status == Status::ENABLE ? Status::DISABLE : Status::ENABLE;
        } catch (\Throwable $th) {
            abort(404);
        }

        $general->socialite_credentials = $credentials;
        $general->save();

        $notify[] = ['success', 'Status changed successfully'];
        return back()->withNotify($notify);
    }

    public function updateSocialiteCredential(Request $request, $key)
    {
        $general     = gs();
        $credentials = $general->socialite_credentials;
        try {
            @$credentials->$key->client_id     = $request->client_id;
            @$credentials->$key->client_secret = $request->client_secret;
        } catch (\Throwable $th) {
            abort(404);
        }
        $general->socialite_credentials = $credentials;
        $general->save();

        $notify[] = ['success', ucfirst($key) . ' credential updated successfully'];
        return back()->withNotify($notify);
    }
}
