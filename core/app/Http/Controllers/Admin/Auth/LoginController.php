<?php
namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Laramin\Utility\Onumoti;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login / registration.
     * All admins land on dashboard; the dashboard view shows only sections they have access to.
     *
     * @return string
     */
    public function redirectTo(): string
    {
        return route('admin.dashboard');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        $pageTitle = "Admin Login";
        return view('admin.auth.login', compact('pageTitle'));
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return auth()->guard('admin');
    }

    public function username()
    {
        return 'username';
    }

    protected function credentials(Request $request): array
    {
        return [
            $this->username() => $request->input($this->username()),
            'password' => $request->input('password'),
            'status' => Admin::STATUS_ENABLED,
        ];
    }

    public function login(Request $request)
    {
        try {
            $this->validateLogin($request);

            $request->session()->regenerateToken();

            if (function_exists('verifyCaptcha') && !verifyCaptcha()) {
                $notify[] = ['error','Invalid captcha provided'];
                return back()->withNotify($notify);
            }

            // Try to get Onumoti data, but don't fail login if it errors
            try {
                Onumoti::getData();
            } catch (\Exception $e) {
                // Log the error but continue with login
                \Log::warning('Onumoti::getData() failed during admin login', [
                    'error' => $e->getMessage(),
                    'ip' => $request->ip()
                ]);
            }

            // If the class is using the ThrottlesLogins trait, we can automatically throttle
            // the login attempts for this application. We'll key this by the username and
            // the IP address of the client making these requests into this application.
            if (method_exists($this, 'hasTooManyLoginAttempts') &&
                $this->hasTooManyLoginAttempts($request)) {
                $this->fireLockoutEvent($request);
                return $this->sendLockoutResponse($request);
            }

            if ($this->attemptLogin($request)) {
                if (function_exists('log_critical')) {
                    log_critical('admin_login', true, ['username' => $request->username]);
                } else {
                    \Log::info('Critical process succeeded: admin_login', ['username' => $request->username, 'ip' => $request->ip()]);
                }
                return $this->sendLoginResponse($request);
            }

            $this->incrementLoginAttempts($request);
            if (function_exists('log_critical')) {
                log_critical('admin_login', false, ['username' => $request->username]);
            } else {
                \Log::error('Critical process failed: admin_login', ['username' => $request->username, 'ip' => $request->ip()]);
            }
            return $this->sendFailedLoginResponse($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors - return with proper error messages
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if (function_exists('log_critical')) {
                log_critical('admin_login', false, ['username' => $request->username ?? 'unknown', 'error' => $e->getMessage()]);
            } else {
                \Log::error('Admin login error: ' . $e->getMessage(), [
                    'username' => $request->username ?? 'unknown',
                    'ip_address' => $request->ip(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            $notify[] = ['error', 'An error occurred during login. Please try again.'];
            return back()->withNotify($notify)->withInput();
        }
    }


    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->loggedOut($request) ?: redirect()->route('admin.login');
    }
}
