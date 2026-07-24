<?php

namespace App\Http\Controllers\Eden;

use App\Http\Controllers\Controller;
use App\Rules\SensiblePersonName;
use App\Support\Seo\EdenSeo;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('eden.layout', array_merge([
            'title' => 'Log in',
            'content' => view('eden.auth.login')->render(),
            'scripts' => '',
        ], EdenSeo::forAuthPage('/login')));
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('web')->attempt($request->only('email', 'password'), (bool) $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('founder.dashboard'));
        }

        $user = User::where('email', $request->email)->first();
        $message = __('The provided credentials do not match our records.');
        if ($user && $user->auth_provider === 'linkedin') {
            $message = 'You created this account with LinkedIn. Please use the LinkedIn button to log in.';
        }

        return redirect()->route('login')->withErrors(['email' => $message])->withInput($request->only('email'));
    }

    public function showRegisterForm(): View
    {
        return view('eden.layout', array_merge([
            'title' => 'Sign up',
            'content' => view('eden.auth.register')->render(),
            'scripts' => '',
        ], EdenSeo::forAuthPage('/register')));
    }

    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:80', new SensiblePersonName()],
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        Auth::guard('web')->login($user);
        $request->session()->regenerate();
        return redirect()->intended('/founder');
    }

    public function logout(Request $request): RedirectResponse
    {
        $wasImpersonating = $request->session()->has('eden_impersonator_admin_id');

        Auth::guard('web')->logout();
        $request->session()->forget('eden_impersonator_admin_id');

        if ($wasImpersonating && Auth::guard('admin')->check()) {
            return redirect()->route('admin.users.index')
                ->with('notify', [['success', 'Returned to admin. User session ended.']]);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
