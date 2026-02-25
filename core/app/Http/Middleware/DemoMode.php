<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to block all write operations for demo/test admin user.
 * 
 * This middleware prevents the test admin from making any changes to the system.
 * It blocks POST, PUT, PATCH, and DELETE requests, allowing only GET requests
 * for viewing purposes.
 */
class DemoMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('admin')->user();
        
        // Check if current admin is the demo/test admin
        if ($admin && $admin->isDemoUser()) {
            // Block all write operations (including method spoofing via _method)
            $method = $request->method();
            $spoofedMethod = $request->input('_method');
            
            // Check both actual method and spoofed method
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
        }
        
        return $next($request);
    }
}
