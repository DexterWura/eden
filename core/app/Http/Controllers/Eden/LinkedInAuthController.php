<?php

namespace App\Http\Controllers\Eden;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class LinkedInAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        if (!$this->configure()) {
            return redirect(url('/login'))->withErrors(['linkedin' => 'LinkedIn login is not configured.']);
        }

        if ($request->has('redirect')) {
            $redirect = $request->query('redirect');
            if ($this->isSafeRedirect($redirect)) {
                session()->put('linkedin_auth_redirect', $redirect);
            }
        }

        return Socialite::driver('linkedin-openid')->redirect();
    }

    public function callback(Request $request)
    {
        if (!$this->configure()) {
            return redirect(url('/login'))->withErrors(['linkedin' => 'LinkedIn login is not configured.']);
        }

        if (!$request->filled('code')) {
            return redirect(url('/login'))->withErrors(['linkedin' => 'LinkedIn authorization was cancelled.']);
        }

        try {
            $linkedinUser = Socialite::driver('linkedin-openid')->user();
        } catch (\Exception $e) {
            return redirect(url('/login'))->withErrors(['linkedin' => 'Could not authenticate with LinkedIn. Please try again.']);
        }

        $email = $linkedinUser->getEmail();
        if (empty($email)) {
            return redirect(url('/login'))->withErrors(['linkedin' => 'LinkedIn did not provide an email address.']);
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            Auth::guard('web')->login($user, true);
            $request->session()->regenerate();
        } else {
            $name = $linkedinUser->getName() ?: ($linkedinUser->getNickname() ?: 'User');

            $user = new User();
            $user->name = $name;
            $user->email = $email;
            $user->password = Hash::make(Str::random(32));
            $user->auth_provider = 'linkedin';
            $user->save();

            Auth::guard('web')->login($user, true);
            $request->session()->regenerate();
        }

        $redirectTo = session()->pull('linkedin_auth_redirect', route('founder.dashboard'));
        if (!$this->isSafeRedirect($redirectTo)) {
            $redirectTo = route('founder.dashboard');
        }
        return redirect($redirectTo);
    }

    private function configure(): bool
    {
        $general = function_exists('gs') ? gs() : null;
        $clientId = $general->linkedin_client_id ?? null;
        $clientSecret = $general->linkedin_client_secret ?? null;

        if (empty($clientId) || empty($clientSecret)) {
            return false;
        }

        Config::set('services.linkedin-openid', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect' => url('/auth/linkedin/callback'),
        ]);

        return true;
    }

    public static function isConfigured(): bool
    {
        $general = function_exists('gs') ? gs() : null;
        return $general
            && !empty(trim((string) ($general->linkedin_client_id ?? '')))
            && !empty(trim((string) ($general->linkedin_client_secret ?? '')));
    }

    private function isSafeRedirect(?string $url): bool
    {
        if ($url === null || trim($url) === '') {
            return false;
        }
        $url = trim($url);
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return true;
        }
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $parsed = parse_url($url);
        $urlHost = $parsed['host'] ?? null;
        return $urlHost !== null && strtolower($urlHost) === strtolower($appHost);
    }
}
