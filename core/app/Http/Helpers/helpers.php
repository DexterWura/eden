<?php

use App\Constants\Status;
use App\Lib\GoogleAuthenticator;
use App\Models\Admin;
use App\Models\Escrow;
use App\Models\Extension;
use App\Models\Frontend;
use App\Models\GeneralSetting;
use App\Models\Listing;
use Carbon\Carbon;
use App\Lib\Captcha;
use App\Lib\ClientInfo;
use App\Lib\CurlRequest;
use App\Lib\FileManager;
use App\Notify\Notify;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Laramin\Utility\VugiChugi;

function systemDetails()
{
    // Get system name from general settings, fallback to default
    $systemName = gs('site_name') ?? 'sellit';
    $system['name']          = $systemName;
    $system['version']       = '1.0';
    $system['build_version'] = '1.0.0';
    return $system;
}

function slug($string)
{
    return Str::slug($string);
}

/**
 * Normalize a URL - add protocol if missing, remove trailing slashes, etc.
 */
function normalizeUrl($url)
{
    if (empty($url)) {
        return null;
    }
    
    $url = trim($url);
    
    // Remove trailing slashes (except after protocol)
    $url = rtrim($url, '/');
    
    // Add protocol if missing
    if (!preg_match('/^https?:\/\//i', $url)) {
        // Check if it looks like a domain
        if (preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9]?\.[a-zA-Z]{2,}/', $url)) {
            $url = 'https://' . $url;
        }
    }
    
    return $url;
}

/**
 * Extract clean domain name from URL
 */
function extractDomain($url)
{
    if (empty($url)) {
        return null;
    }
    
    $url = normalizeUrl($url);
    
    try {
        $parsed = parse_url($url);
        if (!isset($parsed['host'])) {
            return null;
        }
        
        $domain = $parsed['host'];
        
        // Remove www. prefix
        $domain = preg_replace('/^www\./i', '', $domain);
        
        // Remove port if present
        $domain = explode(':', $domain)[0];
        
        return strtolower($domain);
    } catch (\Exception $e) {
        return null;
    }
}

/**
 * Normalize request host for comparison with stored domain_name (lowercase, no www, no port).
 */
function normalizeRequestHost($host)
{
    if (empty($host) || !is_string($host)) {
        return null;
    }
    $host = strtolower(trim($host));
    $host = preg_replace('/^www\./i', '', $host);
    $host = explode(':', $host)[0];
    return $host;
}

/**
 * Platform domain (host only) from APP_URL. Used for domain redirect middleware and blocking platform from being listed.
 */
function platform_domain()
{
    $url = config('app.url');
    if (empty($url)) {
        return null;
    }
    $host = parse_url($url, PHP_URL_HOST);
    return $host ? normalizeRequestHost($host) : null;
}

/**
 * Check if a domain/URL is accessible
 */
function checkDomainAccessibility($url, $timeout = 5)
{
    if (empty($url)) {
        return ['accessible' => false, 'error' => 'URL is empty'];
    }
    
    $url = normalizeUrl($url);
    
    try {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; DomainChecker/1.0)',
            CURLOPT_NOBODY => true, // HEAD request only
        ]);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 400) {
            return ['accessible' => true, 'http_code' => $httpCode];
        } else {
            return [
                'accessible' => false,
                'error' => $curlError ?: "HTTP $httpCode",
                'http_code' => $httpCode
            ];
        }
    } catch (\Exception $e) {
        return ['accessible' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Format amount with helpful context
 */
function formatAmountWithContext($amount, $currency = null)
{
    $currency = $currency ?? gs('cur_text') ?? 'USD';
    $formatted = showAmount($amount);
    
    // Add helpful context for large amounts
    if ($amount >= 1000000) {
        return $formatted . ' (' . number_format($amount / 1000000, 2) . 'M)';
    } elseif ($amount >= 1000) {
        return $formatted . ' (' . number_format($amount / 1000, 2) . 'K)';
    }
    
    return $formatted;
}

/**
 * Get helpful tip based on context
 */
function getHelpfulTip($context, $data = [])
{
    $tips = [
        'low_balance' => 'Consider depositing funds to avoid payment delays',
        'high_bid' => 'Make sure you have sufficient funds if you win this auction',
        'first_listing' => 'Complete your profile and verify your account for better visibility',
        'expired_offer' => 'Offers expire after 7 days. Make a new offer if still interested',
        'pending_verification' => 'Domain verification usually takes a few minutes after uploading the file',
    ];
    
    return $tips[$context] ?? null;
}

/**
 * Validate and suggest better input
 */
function suggestBetterInput($field, $value, $type = 'text')
{
    $suggestions = [];
    
    switch ($type) {
        case 'email':
            $value = strtolower(trim($value));
            if (strpos($value, '@gmail.com') !== false) {
                // Remove dots before @ for Gmail
                $value = str_replace('.', '', substr($value, 0, strpos($value, '@'))) . '@gmail.com';
            }
            break;
            
        case 'phone':
            // Remove common formatting
            $value = preg_replace('/[^0-9+]/', '', $value);
            break;
            
        case 'url':
            $value = normalizeUrl($value);
            break;
            
        case 'name':
            $value = ucwords(strtolower(trim($value)));
            break;
    }
    
    return $value;
}

function verificationCode($length)
{
    if ($length == 0) return 0;
    $min = pow(10, $length - 1);
    $max = (int) ($min - 1).'9';
    return random_int($min,$max);
}

function getNumber($length = 8)
{
    $characters = '1234567890';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


function activeTemplate($asset = false) {
    try {
        $template = session('template') ?? gs('active_template');
        // Fallback if gs() returns null
        if (empty($template)) {
            $template = 'basic';
        }
        if ($asset) return 'assets/templates/' . $template . '/';
        return 'templates.' . $template . '.';
    } catch (\Exception $e) {
        // Fallback template
        if ($asset) return 'assets/templates/basic/';
        return 'templates.basic.';
    }
}

function activeTemplateName() {
    try {
        $template = session('template') ?? gs('active_template');
        // Fallback if gs() returns null
        if (empty($template)) {
            $template = 'basic';
        }
        return $template;
    } catch (\Exception $e) {
        return 'basic';
    }
}

function siteLogo($type = null) {
    $name = $type ? "/logo_$type.png" : '/logo.png';
    return getImage(getFilePath('logo_icon') . $name);
}
function siteFavicon() {
    // Prefer a favicon placed in the public/images directory
    $publicFaviconPng = public_path('images/favicon.png');
    $publicFaviconIco = public_path('images/favicon.ico');

    if (file_exists($publicFaviconIco)) {
        return asset('images/favicon.ico');
    }

    if (file_exists($publicFaviconPng)) {
        return asset('images/favicon.png');
    }

    // Fallback to configured logo_icon location
    return getImage(getFilePath('logo_icon') . '/favicon.png');
}

function loadReCaptcha()
{
    return Captcha::reCaptcha();
}

function loadCustomCaptcha($width = '100%', $height = 46, $bgColor = '#003')
{
    return Captcha::customCaptcha($width, $height, $bgColor);
}

function verifyCaptcha()
{
    return Captcha::verify();
}

function loadExtension($key)
{
    $extension = Extension::where('act', $key)->where('status', Status::ENABLE)->first();
    return $extension ? $extension->generateScript() : '';
}

function getTrx($length = 12)
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function getAmount($amount, $length = 2)
{
    $amount = round($amount ?? 0, $length);
    return $amount + 0;
}

function showAmount($amount, $decimal = 2, $separate = true, $exceptZeros = false, $currencyFormat = true)
{
    $num = (float) ($amount ?? 0);
    $abs = abs($num);
    $printAmount = null;

    if ($abs >= 100000) {
        $units = [
            ['t', 1e12],
            ['b', 1e9],
            ['m', 1e6],
            ['k', 1e3],
        ];
        $sign = $num < 0 ? '-' : '';
        foreach ($units as [$suffix, $divisor]) {
            if ($abs >= $divisor) {
                $val = $num / $divisor;
                $printAmount = $sign . (round($val, 1) == (int) round($val, 1)
                    ? (string) (int) $val
                    : number_format(round($val, 1), 1, '.', ''));
                $printAmount .= strtoupper($suffix);
                break;
            }
        }
    }

    if ($printAmount === null) {
        $separator = $separate ? ',' : '';
        $printAmount = number_format($num, $decimal, '.', $separator);
        if ($exceptZeros) {
            $exp = explode('.', $printAmount);
            if ($exp[1] * 1 == 0) {
                $printAmount = $exp[0];
            } else {
                $printAmount = rtrim($printAmount, '0');
            }
        }
    }

    if ($currencyFormat) {
        if (gs('currency_format') == Status::CUR_BOTH) {
            return gs('cur_sym').$printAmount.' '.__(gs('cur_text'));
        }elseif(gs('currency_format') == Status::CUR_TEXT){
            return $printAmount.' '.__(gs('cur_text'));
        }else{
            return gs('cur_sym').$printAmount;
        }
    }
    return $printAmount;
}


function removeElement($array, $value)
{
    return array_diff($array, (is_array($value) ? $value : array($value)));
}

function cryptoQR($wallet)
{
    return "https://api.qrserver.com/v1/create-qr-code/?data=$wallet&size=300x300&ecc=m";
}

function keyToTitle($text)
{
    return ucfirst(preg_replace("/[^A-Za-z0-9 ]/", ' ', $text));
}


function titleToKey($text)
{
    return strtolower(str_replace(' ', '_', $text));
}

/**
 * Return 6 trending listing tags for the hero section (by active listing count), with fallback.
 * Admin-configured fallback words (hero section) can be passed; otherwise default is used.
 *
 * @param array|null $fallbackWords Up to 6 words used when there aren't enough listing-based tags (e.g. from hero trending_fallback).
 */
function getTrendingListingTags(?array $fallbackWords = null): array
{
    $fallback = $fallbackWords !== null && count($fallbackWords) > 0
        ? array_slice($fallbackWords, 0, 6)
        : ['SaaS', 'Blogs', 'Shopify', 'Youtube', 'Ads', 'Store'];
    $cacheKey = 'trending_listing_tags_' . md5(implode(',', $fallback));

    return Cache::remember($cacheKey, 600, function () use ($fallback) {
        $candidates = $fallback;

        $withCounts = [];
        foreach ($candidates as $term) {
            $count = Listing::active()->search($term)->count();
            $withCounts[] = ['label' => $term, 'search' => $term, 'count' => $count];
        }

        usort($withCounts, fn($a, $b) => $b['count'] <=> $a['count']);
        $topByCount = array_filter($withCounts, fn($item) => $item['count'] > 0);
        $top = array_slice($topByCount, 0, 6);
        $topLabels = array_column($top, 'label');

        $result = array_map(fn($item) => ['label' => $item['label'], 'search' => $item['search']], $top);
        foreach ($fallback as $term) {
            if (count($result) >= 6) {
                break;
            }
            if (!in_array($term, $topLabels)) {
                $result[] = ['label' => $term, 'search' => $term];
            }
        }

        return array_slice($result, 0, 6);
    });
}


function strLimit($title = null, $length = 10)
{
    return Str::limit($title, $length);
}


function getIpInfo()
{
    $ipInfo = ClientInfo::ipInfo();
    return $ipInfo;
}


function osBrowser()
{
    $osBrowser = ClientInfo::osBrowser();
    return $osBrowser;
}


function getTemplates()
{
    $param['purchasecode'] = env("PURCHASECODE");
    $param['website'] = @$_SERVER['HTTP_HOST'] . @$_SERVER['REQUEST_URI'] . ' - ' . env("APP_URL");
    $url = VugiChugi::gttmp() . systemDetails()['name'];
    $response = CurlRequest::curlPostContent($url, $param);
    if ($response) {
        return $response;
    } else {
        return null;
    }
}


function getPageSections($arr = false)
{
    $jsonUrl = resource_path('views/') . str_replace('.', '/', activeTemplate()) . 'sections.json';
    $sections = json_decode(file_get_contents($jsonUrl));
    if ($arr) {
        $sections = json_decode(file_get_contents($jsonUrl), true);
        ksort($sections);
    }
    return $sections;
}


function getImage($image, $size = null)
{
    $clean = '';
    if (file_exists($image) && is_file($image)) {
        return asset($image) . $clean;
    }
    if ($size && \Illuminate\Support\Facades\Route::has('placeholder.image')) {
        return route('placeholder.image', $size);
    }
    return asset('assets/images/default.png');
}


function notify($user, $templateName, $shortCodes = null, $sendVia = null, $createLog = true,$pushImage = null)
{
    $globalShortCodes = [
        'site_name' => gs('site_name'),
        'site_currency' => gs('cur_text'),
        'currency_symbol' => gs('cur_sym'),
    ];

    if (gettype($user) == 'array') {
        $user = (object) $user;
    }

    // Respect user notification preferences (skip if they turned this type off)
    if (isset($user->id) && $templateName) {
        $userModel = $user instanceof \App\Models\User ? $user : \App\Models\User::find($user->id);
        if ($userModel && !$userModel->wantsNotification($templateName)) {
            return;
        }
    }

    // Add user fullname and username so they appear in email/SMS/push templates ({{fullname}}, {{username}})
    if (isset($user->fullname) || isset($user->username)) {
        $globalShortCodes['fullname'] = $user->fullname ?? $user->username ?? '';
        $globalShortCodes['username'] = $user->username ?? $user->fullname ?? '';
    }

    $shortCodes = array_merge($shortCodes ?? [], $globalShortCodes);

    $notify = new Notify($sendVia);
    $notify->templateName = $templateName;
    $notify->shortCodes = $shortCodes;
    $notify->user = $user;
    $notify->createLog = $createLog;
    $notify->pushImage = $pushImage;
    $notify->userColumn = isset($user->id) ? $user->getForeignKey() : 'user_id';
    $notify->send();
}

/**
 * Send an email to all super admins (excluding demoadmin and staff).
 * Uses DEFAULT template with subject and HTML message; no notification log.
 */
function notifySuperAdmins(string $subject, string $htmlMessage): void
{
    $admins = Admin::where('is_super_admin', true)
        ->where('username', '!=', 'demoadmin')
        ->where('status', Admin::STATUS_ENABLED)
        ->get();

    if ($admins->isEmpty()) {
        Log::debug('notifySuperAdmins: no recipients');
        return;
    }

    foreach ($admins as $admin) {
        try {
            $user = [
                'email' => $admin->email,
                'username' => $admin->username,
                'fullname' => $admin->name ?? $admin->username,
            ];
            notify($user, 'DEFAULT', [
                'subject' => $subject,
                'message' => $htmlMessage,
            ], ['email'], createLog: false);
        } catch (\Exception $e) {
            Log::error('notifySuperAdmins send failed', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

/**
 * Notify super admins when an escrow is accepted (sale in escrow).
 */
function notifySuperAdminsForEscrowAccepted(Escrow $escrow): void
{
    $siteName = gs('site_name') ?? 'Marketplace';
    $title = $escrow->listing ? ($escrow->listing->title ?? $escrow->listing->domain_name ?? 'N/A') : ($escrow->title ?? 'N/A');
    $buyer = $escrow->buyer ? ($escrow->buyer->username ?? $escrow->buyer->email ?? '#' . $escrow->buyer_id) : '#' . $escrow->buyer_id;
    $seller = $escrow->seller ? ($escrow->seller->username ?? $escrow->seller->email ?? '#' . $escrow->seller_id) : '#' . $escrow->seller_id;
    $amount = showAmount($escrow->amount);
    $currency = gs('cur_text');
    $url = url(route('admin.escrow.index'));
    $subject = $siteName . ' – Sale in escrow: ' . $escrow->escrow_number;
    $html = '<p>A sale has entered escrow.</p>';
    $html .= '<ul><li><strong>Escrow:</strong> ' . e($escrow->escrow_number) . '</li>';
    $html .= '<li><strong>Listing/Title:</strong> ' . e($title) . '</li>';
    $html .= '<li><strong>Buyer:</strong> ' . e($buyer) . '</li>';
    $html .= '<li><strong>Seller:</strong> ' . e($seller) . '</li>';
    $html .= '<li><strong>Amount:</strong> ' . $amount . ' ' . $currency . '</li></ul>';
    $html .= '<p><a href="' . e($url) . '">View escrows in admin</a></p>';
    notifySuperAdmins($subject, $html);
}

/**
 * Notify super admins when an escrow is completed (sale finalized).
 */
function notifySuperAdminsForEscrowCompleted(Escrow $escrow): void
{
    $siteName = gs('site_name') ?? 'Marketplace';
    $title = $escrow->listing ? ($escrow->listing->title ?? $escrow->listing->domain_name ?? 'N/A') : ($escrow->title ?? 'N/A');
    $buyer = $escrow->buyer ? ($escrow->buyer->username ?? $escrow->buyer->email ?? '#' . $escrow->buyer_id) : '#' . $escrow->buyer_id;
    $seller = $escrow->seller ? ($escrow->seller->username ?? $escrow->seller->email ?? '#' . $escrow->seller_id) : '#' . $escrow->seller_id;
    $amount = showAmount($escrow->amount);
    $currency = gs('cur_text');
    $url = url(route('admin.escrow.index'));
    $subject = $siteName . ' – Sale finalized: ' . $escrow->escrow_number;
    $html = '<p>A sale has been finalized.</p>';
    $html .= '<ul><li><strong>Escrow:</strong> ' . e($escrow->escrow_number) . '</li>';
    $html .= '<li><strong>Listing/Title:</strong> ' . e($title) . '</li>';
    $html .= '<li><strong>Buyer:</strong> ' . e($buyer) . '</li>';
    $html .= '<li><strong>Seller:</strong> ' . e($seller) . '</li>';
    $html .= '<li><strong>Amount:</strong> ' . $amount . ' ' . $currency . '</li></ul>';
    $html .= '<p><a href="' . e($url) . '">View escrows in admin</a></p>';
    notifySuperAdmins($subject, $html);
}

/**
 * Notify super admins about a serious application error (throttled).
 */
function notifySuperAdminsForError(Throwable $e, array $context = []): void
{
    $cacheKey = 'super_admin_error_email_sent';
    $ttlMinutes = 15;
    if (Cache::get($cacheKey)) {
        return;
    }
    Cache::put($cacheKey, true, now()->addMinutes($ttlMinutes));

    $siteName = gs('site_name') ?? 'Marketplace';
    $subject = $siteName . ' – Application error';
    $html = '<p>An error that may need attention has been logged.</p>';
    $html .= '<ul>';
    $html .= '<li><strong>Exception:</strong> ' . e(get_class($e)) . '</li>';
    $html .= '<li><strong>Message:</strong> ' . e($e->getMessage()) . '</li>';
    $html .= '<li><strong>File:</strong> ' . e($e->getFile()) . ' (line ' . (int) $e->getLine() . ')</li>';
    if (!empty($context['url'])) {
        $html .= '<li><strong>URL:</strong> ' . e($context['url']) . '</li>';
    }
    if (!empty($context['method'])) {
        $html .= '<li><strong>Method:</strong> ' . e($context['method']) . '</li>';
    }
    if (isset($context['user_id'])) {
        $html .= '<li><strong>User ID:</strong> ' . (int) $context['user_id'] . '</li>';
    }
    $html .= '</ul>';
    notifySuperAdmins($subject, $html);
}

function getPaginate($paginate = null)
{
    if (!$paginate) {
        $paginate = gs('paginate_number');
    }
    return $paginate;
}

function paginateLinks($data)
{
    return $data->appends(request()->all())->links();
}


function menuActive($routeName, $type = null, $param = null)
{
    if ($type == 3) $class = 'side-menu--open';
    elseif ($type == 2) $class = 'sidebar-submenu__open';
    else $class = 'active';

    // Support comma-separated patterns (e.g. "admin.listing*,admin.marketplace*") so parent dropdown stays open when any child is active
    if (is_string($routeName) && str_contains($routeName, ',')) {
        $routeName = array_map('trim', explode(',', $routeName));
    }

    if (is_array($routeName)) {
        foreach ($routeName as $key => $value) {
            if (request()->routeIs($value)) return $class;
        }
    } elseif ($routeName !== null && $routeName !== '') {
        if (request()->routeIs($routeName)) {
            if ($param) {
                $routeParam = array_values(@request()->route()->parameters ?? []);
                if (strtolower(@$routeParam[0]) == strtolower($param)) return $class;
                else return;
            }
            return $class;
        }
    }
}


function fileUploader($file, $location, $size = null, $old = null, $thumb = null,$filename = null)
{
    $fileManager = new FileManager($file);
    $fileManager->path = $location;
    $fileManager->size = $size;
    $fileManager->old = $old;
    $fileManager->thumb = $thumb;
    $fileManager->filename = $filename;
    $fileManager->upload();
    return $fileManager->filename;
}

function fileManager()
{
    return new FileManager();
}

function getFilePath($key)
{
    return fileManager()->$key()->path;
}

function getFileSize($key)
{
    return fileManager()->$key()->size;
}

function getFileExt($key)
{
    return fileManager()->$key()->extensions;
}

function diffForHumans($date)
{
    $lang = session()->get('lang');
    Carbon::setlocale($lang);
    return Carbon::parse($date)->diffForHumans();
}


function showDateTime($date, $format = 'Y-m-d h:i A')
{
    if (!$date) {
        return '-';
    }
    $lang = session()->get('lang');
    Carbon::setlocale($lang);
    return Carbon::parse($date)->translatedFormat($format);
}


function getContent($dataKeys, $singleQuery = false, $limit = null, $orderById = false) {

    $templateName = activeTemplateName();
    if ($singleQuery) {
        $content = Frontend::where('tempname', $templateName)->where('data_keys', $dataKeys)->orderBy('id', 'desc')->first();
        if (!$content && str_starts_with($dataKeys, 'marketplace_')) {
            $content = Frontend::where('data_keys', $dataKeys)->orderBy('id', 'desc')->first();
        }
    } else {
        $article = Frontend::where('tempname', $templateName);
        $article->when($limit != null, function ($q) use ($limit) {
            return $q->limit($limit);
        });
        if ($orderById) {
            $content = $article->where('data_keys', $dataKeys)->orderBy('id')->get();
        } else {
            $content = $article->where('data_keys', $dataKeys)->orderBy('id', 'desc')->get();
        }
    }
    return $content;
}

function verifyG2fa($user, $code, $secret = null)
{
    $authenticator = new GoogleAuthenticator();
    if (!$secret) {
        $secret = $user->tsc;
    }
    $oneCode = $authenticator->getCode($secret);
    $userCode = $code;
    if ($oneCode == $userCode) {
        $user->tv = Status::YES;
        $user->save();
        return true;
    } else {
        return false;
    }
}


function urlPath($routeName, $routeParam = null)
{
    if ($routeParam == null) {
        $url = route($routeName);
    } else {
        $url = route($routeName, $routeParam);
    }
    $basePath = route('home');
    $path = str_replace($basePath, '', $url);
    return $path;
}


function showMobileNumber($number)
{
    $length = strlen($number);
    return substr_replace($number, '***', 2, $length - 4);
}

function showEmailAddress($email)
{
    $endPosition = strpos($email, '@') - 1;
    return substr_replace($email, '***', 1, $endPosition);
}


function getRealIP()
{
    $ip = $_SERVER["REMOTE_ADDR"];
    //Deep detect ip
    if (filter_var(@$_SERVER['HTTP_FORWARDED'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_FORWARDED'];
    }
    if (filter_var(@$_SERVER['HTTP_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_FORWARDED_FOR'];
    }
    if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    if (filter_var(@$_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    }
    if (filter_var(@$_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    if ($ip == '::1') {
        $ip = '127.0.0.1';
    }

    return $ip;
}


function appendQuery($key, $value)
{
    return request()->fullUrlWithQuery([$key => $value]);
}

function dateSort($a, $b)
{
    return strtotime($a) - strtotime($b);
}

function dateSorting($arr)
{
    usort($arr, "dateSort");
    return $arr;
}

function gs($key = null)
{
    try {
        $general = Cache::get('GeneralSetting');
        if (!$general) {
            // Check if database is available
            try {
                \DB::connection()->getPdo();
                $general = GeneralSetting::first();
                if ($general) {
                    Cache::put('GeneralSetting', $general);
                }
            } catch (\Exception $e) {
                // Database not ready, return null
                return null;
            }
        }
        if ($key) return @$general->$key;
        return $general;
    } catch (\Exception $e) {
        // Return null if anything fails
        return null;
    }
}
function isImage($string){
    $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');
    $fileExtension = pathinfo($string, PATHINFO_EXTENSION);
    if (in_array($fileExtension, $allowedExtensions)) {
        return true;
    } else {
        return false;
    }
}

function isHtml($string)
{
    if (preg_match('/<.*?>/', $string)) {
        return true;
    } else {
        return false;
    }
}


function convertToReadableSize($size) {
    preg_match('/^(\d+)([KMG])$/', $size, $matches);
    $size = (int)$matches[1];
    $unit = $matches[2];

    if ($unit == 'G') {
        return $size.'GB';
    }

    if ($unit == 'M') {
        return $size.'MB';
    }

    if ($unit == 'K') {
        return $size.'KB';
    }

    return $size.$unit;
}


function frontendImage($sectionName, $image, $size = null,$seo = false)
{
    if ($seo) {
        return getImage('assets/images/frontend/' . $sectionName . '/seo/' . $image, $size);
    }
    return getImage('assets/images/frontend/' . $sectionName . '/' . $image, $size);
}

/**
 * Convert number to short format (K, M, B)
 * @param int|float $number
 * @param int $precision
 * @return string
 */
function shortNumber($number, $precision = 1)
{
    if ($number < 1000) {
        return number_format($number, 0);
    }

    $suffixes = ['', 'K', 'M', 'B', 'T'];
    $suffixIndex = 0;

    while ($number >= 1000 && $suffixIndex < count($suffixes) - 1) {
        $number /= 1000;
        $suffixIndex++;
    }

    $formattedNumber = number_format($number, $precision);
    // Remove trailing zeros after decimal point
    $formattedNumber = rtrim(rtrim($formattedNumber, '0'), '.');

    return $formattedNumber . $suffixes[$suffixIndex];
}

/**
 * Generate unique listing number
 * @return string
 */
function generateListingNumber()
{
    return 'LST' . date('Ymd') . strtoupper(Str::random(6));
}

/**
 * Generate unique bid number
 * @return string
 */
function generateBidNumber()
{
    return 'BID' . date('Ymd') . strtoupper(Str::random(6));
}

/**
 * Check if a country has states/provinces
 * Countries with states: USA, Canada, Australia, India, Brazil, Mexico, Germany, etc.
 * @param string $countryCode Two-letter ISO country code (e.g., 'US', 'ZW')
 * @return bool
 */
function countryHasStates($countryCode)
{
    if (empty($countryCode)) {
        return false;
    }
    
    $countryCode = strtoupper(trim($countryCode));
    
    // List of countries that have states/provinces
    $countriesWithStates = [
        'US', // United States
        'CA', // Canada
        'AU', // Australia
        'IN', // India
        'BR', // Brazil
        'MX', // Mexico
        'DE', // Germany (Länder)
        'AR', // Argentina
        'NG', // Nigeria
        'ZA', // South Africa
        'RU', // Russia
        'CN', // China
        'ID', // Indonesia
        'MY', // Malaysia
        'PH', // Philippines
        'PK', // Pakistan
        'BD', // Bangladesh
        'VN', // Vietnam
        'TH', // Thailand
        'IT', // Italy
        'ES', // Spain
        'FR', // France
        'GB', // United Kingdom (counties/regions)
        'JP', // Japan
        'KR', // South Korea
    ];
    
    return in_array($countryCode, $countriesWithStates);
}

/**
 * Get list of country codes that have states as JSON for JavaScript
 * @return string JSON encoded array
 */
function getCountriesWithStatesJson()
{
    $countriesWithStates = [
        'US', 'CA', 'AU', 'IN', 'BR', 'MX', 'DE', 'AR', 'NG', 'ZA',
        'RU', 'CN', 'ID', 'MY', 'PH', 'PK', 'BD', 'VN', 'TH', 'IT',
        'ES', 'FR', 'GB', 'JP', 'KR'
    ];
    
    return json_encode($countriesWithStates);
}

/**
 * Mask sensitive data for demo mode.
 * Returns protected text if demo user is logged in, otherwise returns original value.
 * 
 * @param mixed $value
 * @return mixed
 */
function maskForDemo($value)
{
    if (empty($value)) {
        return $value;
    }
    
    try {
        $admin = auth('admin')->user();
        if ($admin && method_exists($admin, 'isDemoUser') && $admin->isDemoUser()) {
            return '{protected in demo mode}';
        }
    } catch (\Exception $e) {
        // If auth fails, return original value
    }
    
    return $value;
}

/**
 * Generate unique offer number
 * @return string
 */
function generateOfferNumber()
{
    return 'OFR' . date('Ymd') . strtoupper(Str::random(6));
}

/**
 * Resolve current admin route name to a module key (sidenav section).
 * Used for staff module access control. Returns null if no module matches.
 *
 * @param string|null $routeName Current route name (defaults to request()->route()?->getName())
 * @return string|null Module key or null
 */
function resolveAdminModuleFromRoute(?string $routeName = null): ?string
{
    $routeName = $routeName ?? request()->route()?->getName();
    if ($routeName === null || $routeName === '') {
        return null;
    }

    $routePatterns = Cache::remember('admin_module_route_patterns', 3600, function () {
        $patterns = config('admin_modules.route_patterns', []);
        // Sort by pattern length descending so more specific patterns match first
        uksort($patterns, function ($a, $b) {
            return strlen($b) <=> strlen($a);
        });
        return $patterns;
    });

    foreach ($routePatterns as $pattern => $moduleKey) {
        if (Str::is($pattern, $routeName)) {
            return $moduleKey;
        }
    }

    return null;
}

/**
 * Log an admin/staff action to the audit log.
 *
 * @param string $action Action identifier (e.g. 'staff.created', 'user.updated', 'listing.approved')
 * @param string|null $description Human-readable description
 * @param object|string|null $subject Subject model (for morph) or null
 * @param array $oldValues Previous values (for updates)
 * @param array $newValues New values (for updates/creates)
 * @return \App\Models\AdminAuditLog|null
 */
function admin_audit_log(
    string $action,
    ?string $description = null,
    $subject = null,
    array $oldValues = [],
    array $newValues = []
) {
    try {
        $admin = auth('admin')->user();
        $request = request();
        $subjectType = null;
        $subjectId = null;
        if (is_object($subject)) {
            $subjectType = get_class($subject);
            $subjectId = $subject->getKey();
        }

        return \App\Models\AdminAuditLog::create([
            'admin_id' => $admin?->id,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'description' => $description,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'route_name' => $request?->route()?->getName(),
            'request_method' => $request?->method(),
        ]);
    } catch (\Throwable $e) {
        \Log::warning('admin_audit_log failed: ' . $e->getMessage());
        return null;
    }
}

/**
 * Log a critical process outcome (success or failure) using Laravel's logger.
 * Use for installer, auth, payments, and other critical flows.
 *
 * @param string $process Process name (e.g. 'install_step4', 'admin_login', 'user_register')
 * @param bool $success True if the process succeeded, false if it failed
 * @param array $context Extra context (e.g. ['user_id' => 1, 'step' => 'migrations'])
 */
function log_critical(string $process, bool $success, array $context = []): void
{
    try {
        $context['process'] = $process;
        $context['success'] = $success;
        $context['url'] = request()?->fullUrl();
        $context['method'] = request()?->method();
        $context['ip'] = request()?->ip();
        if ($success) {
            Log::info("Critical process succeeded: {$process}", $context);
        } else {
            Log::error("Critical process failed: {$process}", $context);
        }
    } catch (\Throwable $e) {
        Log::warning('log_critical failed: ' . $e->getMessage());
    }
}
