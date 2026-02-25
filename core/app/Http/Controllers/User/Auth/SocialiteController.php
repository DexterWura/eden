<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Lib\SocialLogin;

class SocialiteController extends Controller
{

    public function socialLogin($provider)
    {
        // Normal social login - does NOT set any session flags
        // This ensures normal login/register flows work independently
        // Session flags are only set by ListingController::redirectToAccountOAuth() for account data fetch.
        // Ownership verification uses its own callback route (user.ownership.validation.oauth.callback).
        $socialLogin = new SocialLogin($provider);
        return $socialLogin->redirectDriver();
    }


    public function callback($provider)
    {
        // ============================================================
        // OAuth CALLBACK ROUTING LOGIC (login callback only)
        // ============================================================
        // This callback handles TWO flows:
        // 1. Account Data Fetch (listing creation) - checks oauth_for_account_data / cache
        // 2. Normal Social Login (login/register) - falls through if not account data fetch
        //
        // Ownership verification uses a dedicated callback route and never hits this URL.
        // ============================================================
        
        $userId = auth()->id();
        
        \Log::info('OAuth callback received', [
            'provider' => $provider,
            'user_id' => $userId,
            'has_oauth_for_account_data_session' => session()->has('oauth_for_account_data'),
            'request_url' => request()->fullUrl(),
            'referer' => request()->header('referer'),
        ]);
        
        // CRITICAL: Check if this OAuth is for account data fetch FIRST
        // Check BOTH session and cache (cache is more reliable across OAuth redirects)
        $isAccountDataFetch = false;
        $cacheKey = null;
        $platform = null;
        
        if ($userId) {
            // Try to find cache key from session first
            $cacheKey = session()->get('oauth_account_data_cache_key');
            
            // If not in session, try to find it by checking common platforms
            if (!$cacheKey) {
                $platforms = ['instagram', 'facebook', 'twitter', 'linkedin'];
                foreach ($platforms as $p) {
                    $key = 'oauth_account_data_' . $userId . '_' . $p;
                    if (\Illuminate\Support\Facades\Cache::has($key)) {
                        $cacheKey = $key;
                        $platform = $p;
                        break;
                    }
                }
            } else {
                // Extract platform from cache key
                $parts = explode('_', $cacheKey);
                if (count($parts) >= 4) {
                    $platform = $parts[3];
                }
            }
            
            // Check if cache exists (more reliable than session)
            if ($cacheKey && \Illuminate\Support\Facades\Cache::has($cacheKey)) {
                $isAccountDataFetch = true;
                \Log::info('Found account data fetch flag in cache', [
                    'cache_key' => $cacheKey,
                    'platform' => $platform,
                ]);
            }
        }
        
        // Also check session as backup
        if (!$isAccountDataFetch && session()->has('oauth_for_account_data')) {
            $isAccountDataFetch = true;
            \Log::info('Found account data fetch flag in session');
        }
        
        if ($isAccountDataFetch) {
            // Get platform from cache if not already determined
            if (!$platform) {
                // Map provider to platform
                $providerToPlatform = [
                    'instagram' => 'instagram',
                    'facebook' => 'facebook',
                    'twitter' => 'twitter',
                    'linkedin' => 'linkedin',
                    'linkedin-openid' => 'linkedin',
                    'google' => 'youtube', // YouTube uses Google OAuth
                ];
                $platform = $providerToPlatform[$provider] ?? $provider;
            }
            
            \Log::info('OAuth callback detected as account data fetch', [
                'provider' => $provider,
                'platform' => $platform,
                'user_id' => $userId,
                'cache_key' => $cacheKey,
            ]);
            
            // Handle LinkedIn OpenID Connect provider name
            $actualProvider = $provider === 'linkedin' ? 'linkedin-openid' : $provider;
            
            // IMPORTANT: Do NOT call SocialLogin::login() - just fetch user data
            // Pass provider to callback so it can get the user WITHOUT logging them in
            try {
                $result = app(\App\Http\Controllers\User\ListingController::class)->handleAccountOAuthCallback($platform, $actualProvider);
                
                // Ensure we return the result and don't fall through to login
                return $result;
            } catch (\Exception $e) {
                \Log::error('Account data fetch callback error', [
                    'provider' => $provider,
                    'platform' => $platform,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                session()->forget('oauth_for_account_data');
                session()->forget('account_data_fetch_context');
                session()->forget('oauth_account_data_cache_key');
                
                // Clear cache if exists
                if ($cacheKey && \Illuminate\Support\Facades\Cache::has($cacheKey)) {
                    \Illuminate\Support\Facades\Cache::forget($cacheKey);
                }
                
                $notify[] = ['error', 'Failed to fetch account data: ' . $e->getMessage()];
                return redirect()->route('user.listing.create')->withNotify($notify);
            }
        }

        // LinkedIn (and other OAuth2) require the authorization code in the callback query string
        if (!request()->filled('code')) {
            $notify[] = ['error', 'Login was cancelled or the authorization code was not received. Please ensure the callback URL in your ' . ucfirst($provider) . ' app settings exactly matches: ' . route('user.social.login.callback', $provider)];
            return to_route('home')->withNotify($notify);
        }

        // IMPORTANT: If user is already authenticated, they might be trying to fetch account data
        // Check if they came from listing creation page (check referer)
        if (auth()->check()) {
            $referer = request()->header('referer');
            $isFromListingCreate = $referer && (
                strpos($referer, route('user.listing.create', [], false)) !== false ||
                strpos($referer, 'listing/create') !== false
            );
            
            // Also check cache for any account data fetch flags
            $userId = auth()->id();
            $platforms = ['instagram', 'facebook', 'twitter', 'linkedin'];
            foreach ($platforms as $p) {
                $key = 'oauth_account_data_' . $userId . '_' . $p;
                if (\Illuminate\Support\Facades\Cache::has($key)) {
                    \Log::warning('Authenticated user OAuth callback - likely account data fetch but flag check missed', [
                        'provider' => $provider,
                        'user_id' => $userId,
                        'cache_key' => $key,
                        'referer' => $referer,
                    ]);
                    // Redirect back to listing creation to avoid unwanted login
                    $notify[] = ['error', 'Please use the OAuth button from the listing creation form.'];
                    return redirect()->route('user.listing.create')->withNotify($notify);
                }
            }
            
            if ($isFromListingCreate) {
                \Log::warning('Authenticated user OAuth callback from listing page - redirecting back', [
                    'provider' => $provider,
                    'user_id' => $userId,
                ]);
                $notify[] = ['error', 'OAuth session expired. Please try connecting your account again from the listing form.'];
                return redirect()->route('user.listing.create')->withNotify($notify);
            }
        }

        // Normal social login - only reached if not account data fetch
        // This is the standard login/register flow for LinkedIn, Facebook, Google, etc.
        $socialLogin = new SocialLogin($provider);
        try {
            return $socialLogin->login();
        } catch (\Exception $e) {
            $notify[] = ['error', $e->getMessage()];
            return to_route('home')->withNotify($notify);
        }
    }
}
