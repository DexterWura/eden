<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAdminReconfirmation
{
    public function handle(Request $request, Closure $next): Response
    {
        $confirmedAt = (int) $request->session()->get('admin_reconfirmed_at', 0);
        if ($confirmedAt > time() - 600) {
            return $next($request);
        }

        $returnTo = '/' . ltrim($request->getRequestUri(), '/');
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            $returnTo = route('admin.dashboard', absolute: false);
        }
        $request->session()->put('admin_reconfirm_return_to', $returnTo);
        return redirect()->route('admin.security.reconfirm');
    }
}
