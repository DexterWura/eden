<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminModuleAccess
{
    /**
     * Ensure the current admin has access to the module for this route.
     * Super admins bypass. Staff must have the resolved module in allowed_modules.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return redirect()->route('admin.login');
        }

        if ($admin->isSuperAdmin()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        // Dashboard is the landing page for all admins: they see only sections they have access to
        if ($routeName === 'admin.dashboard') {
            return $next($request);
        }

        $module = resolveAdminModuleFromRoute($routeName);

        // No module resolved: allow (safer for new routes until assigned to a module)
        if ($module === null) {
            return $next($request);
        }

        if ($admin->hasModule($module)) {
            return $next($request);
        }

        abort(403, 'You do not have access to this section.');
    }
}
