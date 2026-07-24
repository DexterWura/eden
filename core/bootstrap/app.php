<?php

use App\Http\Middleware\CheckAdminModuleAccess;
use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\RedirectIfAdmin;
use App\Http\Middleware\RedirectIfNotAdmin;
use App\Http\Middleware\RequireSuperAdmin;
use App\Http\Middleware\EnsureAdminTwoFactorChallenge;
use App\Http\Middleware\RequireAdminReconfirmation;
use App\Http\Middleware\AuditAdminMutation;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->append(ForceHttps::class);
        $middleware->alias([
            'admin' => RedirectIfNotAdmin::class,
            'admin.guest' => RedirectIfAdmin::class,
            'admin.module' => CheckAdminModuleAccess::class,
            'admin.super' => RequireSuperAdmin::class,
            'admin.2fa' => EnsureAdminTwoFactorChallenge::class,
            'admin.reconfirm' => RequireAdminReconfirmation::class,
            'admin.audit' => AuditAdminMutation::class,
            'eden.revenue.api' => \App\Http\Middleware\AuthenticateRevenueApi::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
