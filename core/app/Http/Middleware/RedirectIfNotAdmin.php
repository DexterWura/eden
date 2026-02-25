<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'admin')
    {
        if (!Auth::guard($guard)->check()) {
            return to_route('admin.login');
        }

        $user = Auth::guard($guard)->user();
        if (!$user->isEnabled()) {
            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $notify[] = ['error', 'Account disabled.'];
            return to_route('admin.login')->withNotify($notify);
        }

        return $next($request);
    }
}
