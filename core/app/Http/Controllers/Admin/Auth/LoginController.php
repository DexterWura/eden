<?php
namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
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
                // Log successful login
                \Log::info('Admin login successful', [
                    'username' => $request->username,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                return $this->sendLoginResponse($request);
            }

            // If the login attempt was unsuccessful we will increment the number of attempts
            // to login and redirect the user back to the login form. Of course, when this
            // user surpasses their maximum number of attempts they will get locked out.
            $this->incrementLoginAttempts($request);

            // Log failed login attempt
            \Log::warning('Admin login failed', [
                'username' => $request->username,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return $this->sendFailedLoginResponse($request);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors - return with proper error messages
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Admin login error: ' . $e->getMessage(), [
                'username' => $request->username ?? 'unknown',
                'ip_address' => $request->ip(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return user-friendly error message
            $notify[] = ['error', 'An error occurred during login. Please try again.'];
            return back()->withNotify($notify)->withInput();
        }
    }


    public function logout(Request $request)
    {
        $this->guard('admin')->logout();
        $request->session()->invalidate();
        return $this->loggedOut($request) ?: redirect()->route('admin.login');
    }
}
