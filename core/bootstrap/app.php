<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'payment/paynow/result',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request): ?Response {
            $msg = $e->getMessage();
            if (str_contains($msg, 'No application encryption key')
                || (str_contains($msg, 'APP_KEY') && str_contains($msg, 'key'))) {
                $html = '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Setup required – Eden</title><style>body{font-family:system-ui,sans-serif;max-width:560px;margin:3rem auto;padding:0 1rem;background:#f5f0e1;}h1{color:#6CAA64;}a{color:#6CAA64;}code{background:#eee;padding:.2em .4em;border-radius:4px;}pre{overflow:auto;background:#eee;padding:1rem;border-radius:4px;}</style></head><body><h1>Setup required</h1><p>Eden is not configured. Create <code>.env</code> and generate a key:</p><pre>cp .env.example .env\nphp artisan key:generate</pre><p>Then open <a href="/install">/install</a> to complete the web installer.</p></body></html>';
                return new Response($html, 500, ['Content-Type' => 'text/html; charset=utf-8']);
            }

            $status = $e instanceof HttpException ? $e->getStatusCode() : 500;
            if (! $request->expectsJson() && $request->isMethod('GET')) {
                if ($status === 500) {
                    return response()->view('errors.500', ['exception' => $e], 500);
                }
                if ($status === 404) {
                    return response()->view('errors.404', ['exception' => $e], 404);
                }
                if ($status === 503) {
                    return response()->view('errors.503', ['exception' => $e], 503);
                }
                if ($status === 403) {
                    return response()->view('errors.403', ['exception' => $e], 403);
                }
            }

            return null;
        });
    })
    ->create();
