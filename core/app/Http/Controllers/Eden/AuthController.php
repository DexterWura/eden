<?php

namespace App\Http\Controllers\Eden;

use App\Http\Controllers\Controller;
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
        return view('eden.layout', [
            'title' => 'Log in',
            'content' => view('eden.auth.login')->render(),
            'scripts' => '',
        ]);
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

        return redirect()->route('login')->withErrors(['email' => __('The provided credentials do not match our records.')])->withInput($request->only('email'));
    }

    public function showRegisterForm(): View
    {
        return view('eden.layout', [
            'title' => 'Sign up',
            'content' => view('eden.auth.register')->render(),
            'scripts' => '',
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|min:2|max:255',
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
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
