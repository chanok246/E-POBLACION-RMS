<?php

use App\Http\Middleware\EnsureFieldPersonnelHasAcceptedPolicies;
use App\Http\Middleware\EnsureFieldPersonnelIsActive;
use App\Http\Middleware\EnsureTwoFactorIsVerified;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Trust the reverse proxy forwarding HTTPS requests to Render.
        $middleware->trustProxies(
            at: '*',
            headers:
                Request::HEADER_X_FORWARDED_FOR |
                Request::HEADER_X_FORWARDED_HOST |
                Request::HEADER_X_FORWARDED_PORT |
                Request::HEADER_X_FORWARDED_PROTO
        );

        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'active' => EnsureUserIsActive::class,
            'twofactor' => EnsureTwoFactorIsVerified::class,

            'fieldpersonnel.active' => EnsureFieldPersonnelIsActive::class,
            'fieldpersonnel.policy' => EnsureFieldPersonnelHasAcceptedPolicies::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
