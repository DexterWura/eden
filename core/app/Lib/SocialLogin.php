<?php

namespace App\Lib;

use App\Constants\Status;
use App\Models\AdminNotification;
use App\Models\User;
use App\Models\UserLogin;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Socialite;

class SocialLogin
{
    private $provider;
    private $fromApi;

    public function __construct($provider,$fromApi = false)
    {
        $this->provider = $provider;
        $this->fromApi = $fromApi;
        $this->configuration();
    }

    /**
     * Socialite driver name: use linkedin-openid for LinkedIn (new OpenID Connect scopes).
     */
    private function getDriverName(): string
    {
        return $this->provider === 'linkedin' ? 'linkedin-openid' : $this->provider;
    }

    public function redirectDriver()
    {
        return Socialite::driver($this->getDriverName())->redirect();
    }

    /**
     * Redirect to OAuth provider using a custom callback URL (e.g. for ownership verification).
     * The given URL must be registered as an allowed redirect URI in the provider's app settings.
     */
    public function redirectDriverTo(string $callbackUrl)
    {
        $this->configuration();
        Config::set('services.' . $this->getDriverName() . '.redirect', $callbackUrl);
        return Socialite::driver($this->getDriverName())->redirect();
    }

    /**
     * Apply credentials and set redirect to the given URL so that a subsequent
     * Socialite::driver()->user() call uses this redirect_uri for the token exchange.
     */
    public function setRedirectForCallback(string $callbackUrl): void
    {
        $this->configuration();
        Config::set('services.' . $this->getDriverName() . '.redirect', $callbackUrl);
    }

    private function configuration()
    {
        $provider      = $this->provider;
        $configuration = gs('socialite_credentials')->$provider;
        $driverName    = $provider === 'linkedin' ? 'linkedin-openid' : $provider;

        // Callback path stays .../callback/linkedin so it matches the URL registered in the LinkedIn app
        $callbackPath = parse_url(route('user.social.login.callback', $provider), PHP_URL_PATH);
        $redirectUrl  = rtrim(config('app.url'), '/') . $callbackPath;

        Config::set('services.' . $driverName, [
            'client_id'     => $configuration->client_id,
            'client_secret' => $configuration->client_secret,
            'redirect'      => $redirectUrl,
        ]);
    }

    public function login()
    {
        $provider   = $this->provider;
        $driverName = $provider === 'linkedin' ? 'linkedin-openid' : $provider;
        $driver     = Socialite::driver($driverName);
        if ($this->fromApi) {
            try {
                $user = (object)$driver->userFromToken(request()->token)->user;
            } catch (\Throwable $th) {
                throw new Exception('Something went wrong');
            }
        } else {
            $user = $driver->user();
        }

        if ($driverName === 'linkedin-openid') {
            $user->id = $user->sub;
        }

        $isNewUser = false;
        
        // LinkedIn: match by email only; fail if no existing user has this email (no auto-create, no login by provider_id alone)
        if ($this->provider === 'linkedin' || $driverName === 'linkedin-openid') {
            $userData = $this->loginLinkedInByEmail($user, $isNewUser);
        } else {
            $userData = User::where('provider_id', $user->id)->where('provider', $this->provider)->first();

            if (!$userData && !empty($user->email)) {
                $userData = User::where('email', $user->email)->first();
                if ($userData) {
                    $userData->provider_id = $user->id;
                    $userData->provider    = $this->provider;
                    $userData->save();
                    $isNewUser = false;
                }
            }

            if (!$userData) {
                if (!gs('registration')) {
                    throw new Exception('New account registration is currently disabled');
                }
                if (!empty($user->email) && User::where('email', $user->email)->exists()) {
                    throw new Exception('Email already exists');
                }
                $userData = $this->createUser($user, $this->provider);
                $isNewUser = true;
            }
        }

        if ($isNewUser) {
            \App\Services\AffiliateService::creditReferrer($userData);
            if ((int) (gs('welcome_email_enable') ?? 0) === 1 && (int) (gs('en') ?? 0) === 1) {
                notify($userData, 'WELCOME_EMAIL', []);
            }
        }

        if ($this->fromApi) {
            $tokenResult = $userData->createToken('auth_token')->plainTextToken;
            $this->loginLog($userData);
            return [
                'user'         => $userData,
                'access_token' => $tokenResult,
                'token_type'   => 'Bearer',
            ];
        }
        Auth::login($userData);
        $this->loginLog($userData);
        
        // If this is a new user created via social login, redirect to profile completion
        if ($isNewUser && $userData->profile_complete == Status::NO) {
            session()->flash('social_signup_provider', $this->provider);
            $notify[] = ['info', 'Welcome! Your account has been created. Please complete your profile information below to continue using our platform.'];
            return redirect()->route('user.data')->withNotify($notify);
        }
        
        $intendedUrl = session()->pull('url.intended');
        if ($intendedUrl) {
            return redirect($intendedUrl);
        }
        
        return to_route('user.home');
    }

    /**
     * LinkedIn login/signup: match by email only (no login by provider_id alone).
     * - If existing user has this email → log them in and link LinkedIn.
     * - If no user has this email and registration is enabled → create new account (signup).
     * - If no user has this email and registration is disabled → fail.
     *
     * @param  object  $user  OAuth user (sub, email, name, etc.)
     * @param  bool  &$isNewUser  Reference to flag indicating if this is a new user
     * @return \App\Models\User
     * @throws \Exception
     */
    private function loginLinkedInByEmail($user, &$isNewUser)
    {
        $email = $user->email ?? null;
        if (empty($email) || !is_string($email)) {
            throw new Exception('LinkedIn did not provide an email. Please ensure your LinkedIn account has a verified email and you have granted email access to this app.');
        }

        $email = trim($email);

        $userData = User::where('email', $email)->first();

        if ($userData) {
            // Existing user: link this LinkedIn account and log them in
            $userData->provider_id = $user->id;
            $userData->provider    = $this->provider;
            $userData->save();
            $isNewUser = false;
            return $userData;
        }

        // No user with this email: signup if registration is enabled
        if (!gs('registration')) {
            throw new Exception('No account found with this email (' . $email . '). Please register first with this email, then you can sign in with LinkedIn.');
        }

        $isNewUser = true;
        return $this->createUser($user, $this->provider);
    }

    private function createUser($user, $provider)
    {
        $general  = gs();
        $password = getTrx(8);

        $firstName = null;
        $lastName = null;

        if (@$user->first_name) {
            $firstName = $user->first_name;
        }
        if (@$user->last_name) {
            $lastName = $user->last_name;
        }

        if ((!$firstName || !$lastName) && @$user->name) {
            $firstName = preg_replace('/\W\w+\s*(\W*)$/', '$1', $user->name);
            $pieces    = explode(' ', $user->name);
            $lastName  = array_pop($pieces);
        }

        $referBy = \App\Services\AffiliateService::getReferrerUsernameFromSession();
        $referUser = $referBy ? User::where('username', $referBy)->first() : null;

        $newUser = new User();
        $newUser->provider_id = $user->id;

        $newUser->email = !empty($user->email) ? $user->email : (str_replace('.', '_', $user->id) . '@linkedin.placeholder');

        $newUser->password = Hash::make($password);
        $newUser->firstname = $firstName;
        $newUser->lastname = $lastName;
        $newUser->ref_by = $referUser ? $referUser->id : 0;

        $newUser->status = Status::VERIFIED;
        $newUser->kv = $general->kv ? Status::NO : Status::YES;
        $newUser->ev = Status::VERIFIED;
        $newUser->sv = gs('sv') ? Status::UNVERIFIED : Status::VERIFIED;
        $newUser->ts = Status::DISABLE;
        $newUser->tv = Status::VERIFIED;
        $newUser->provider = $provider;
        $newUser->profile_complete = Status::NO; // Mark profile as incomplete so they're prompted to complete it
        $newUser->save();

        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $newUser->id;
        $adminNotification->title = 'New member registered';
        $adminNotification->click_url = urlPath('admin.users.detail', $newUser->id);
        $adminNotification->save();

        $user = User::find($newUser->id);

        return $user;
    }

    private function loginLog($user)
    {
        //Login Log Create
        $ip = getRealIP();
        $exist = UserLogin::where('user_ip', $ip)->first();
        $userLogin = new UserLogin();

        //Check exist or not
        if ($exist) {
            $userLogin->longitude =  $exist->longitude;
            $userLogin->latitude =  $exist->latitude;
            $userLogin->city =  $exist->city;
            $userLogin->country_code = $exist->country_code;
            $userLogin->country =  $exist->country;
        } else {
            $info = json_decode(json_encode(getIpInfo()), true);
            $userLogin->longitude =  @implode(',', $info['long']);
            $userLogin->latitude =  @implode(',', $info['lat']);
            $userLogin->city =  @implode(',', $info['city']);
            $userLogin->country_code = @implode(',', $info['code']);
            $userLogin->country =  @implode(',', $info['country']);
        }

        $userAgent = osBrowser();
        $userLogin->user_id = $user->id;
        $userLogin->user_ip =  $ip;

        $userLogin->browser = @$userAgent['browser'];
        $userLogin->os = @$userAgent['os_platform'];
        $userLogin->save();
    }
}
