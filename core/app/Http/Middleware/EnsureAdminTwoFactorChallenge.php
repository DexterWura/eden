<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminTwoFactorChallenge
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = auth('admin')->user();
        if (! $admin?->hasTwoFactorEnabled()) {
            return $next($request);
        }

        $verifiedAdminId = (int) $request->session()->get('admin_2fa_verified_id', 0);
        if ($verifiedAdminId === (int) $admin->id) {
            return $next($request);
        }

        if ($request->routeIs('admin.security.challenge', 'admin.security.challenge.verify', 'admin.logout')) {
            return $next($request);
        }

        return redirect()->route('admin.security.challenge');
    }
}
