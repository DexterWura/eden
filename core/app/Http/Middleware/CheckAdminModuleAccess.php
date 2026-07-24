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
        if (! $admin) {
            return redirect()->route('admin.login');
        }

        if ($admin->isSuperAdmin()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        // These endpoints filter their content by the admin's assigned modules.
        if (in_array($routeName, ['admin.dashboard', 'admin.search'], true)) {
            return $next($request);
        }

        $module = resolveAdminModuleFromRoute($routeName);

        // Staff access is fail-closed so new admin routes cannot bypass RBAC.
        if ($module === null) {
            abort(403, 'This section has not been assigned to an admin module.');
        }

        if ($admin->hasModule($module)) {
            return $next($request);
        }

        abort(403, 'You do not have access to this section.');
    }
}
