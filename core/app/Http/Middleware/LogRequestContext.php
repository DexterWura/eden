<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds a request id + common request context to all logs during this request.
 * Also returns X-Request-Id header to help correlate client reports with logs.
 */
class LogRequestContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $request->headers->get('X-Request-Id') ?: (string) Str::uuid();

        Log::withContext([
            'request_id' => $requestId,
            'route' => optional($request->route())->getName(),
            'path' => $request->path(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_id' => auth()->id(),
        ]);

        /** @var Response $response */
        $response = $next($request);
        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }
}


