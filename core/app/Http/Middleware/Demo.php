<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enhanced demo middleware that blocks write operations.
 * 
 * This middleware blocks POST, PUT, PATCH, and DELETE requests.
 * It also specifically checks for the test admin user and provides
 * appropriate error messages.
 */
class Demo
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $admin = Auth::guard('admin')->user();
        
        // Check if this is the test admin user
        if ($admin && $admin->isDemoUser()) {
            $method = $request->method();
            $spoofedMethod = $request->input('_method');
            
            // Check both actual method and spoofed method (Laravel method spoofing)
            if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE']) || 
                in_array(strtoupper($spoofedMethod ?? ''), ['PUT', 'PATCH', 'DELETE'])) {
                $notify[] = ['error', 'This is demo version. No changes are allowed.'];
                
                // Handle AJAX requests (check for X-Requested-With header or expectsJson)
                if ($request->expectsJson() || 
                    $request->is('api/*') || 
                    $request->ajax() || 
                    $request->wantsJson() ||
                    $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json([
                        'remark' => 'demo_mode',
                        'status' => 'error',
                        'message' => ['error' => ['This is demo version. No changes are allowed.']],
                    ], 403);
                }
                
                return back()->withNotify($notify);
            }
        } else {
            // For other users, show generic demo message
            $method = $request->method();
            $spoofedMethod = $request->input('_method');
            
            if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE']) || 
                in_array(strtoupper($spoofedMethod ?? ''), ['PUT', 'PATCH', 'DELETE'])) {
                $notify[] = ['warning', 'You can not change anything over this demo'];
                $notify[] = ['info', 'This version is for demonstration purposes only and few actions are blocked'];
                return back()->withNotify($notify);
            }
        }
        
        return $next($request);
    }
}
