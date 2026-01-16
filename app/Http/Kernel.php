<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * Global HTTP middleware stack.
     * These run during every request.
     */
    protected $middleware = [
        // Trust proxies / load balancers
        \App\Http\Middleware\TrustProxies::class,

        // CORS handling (Laravel's built-in)
        \Illuminate\Http\Middleware\HandleCors::class,

        // Maintenance mode protection
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,

        // Limit large POST bodies
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,

        // Trim input strings
        \App\Http\Middleware\TrimStrings::class,

        // Convert empty strings to null
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * Route middleware groups.
     */
    protected $middlewareGroups = [
        'web' => [
            // Cookie encryption & queue
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,

            // Session
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,

            // Share validation errors from session to views
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,

            // CSRF protection
            \App\Http\Middleware\VerifyCsrfToken::class,

            // Route-model binding, implicit bindings, etc.
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            // API rate limiting (configure in RouteServiceProvider)
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * Middleware aliases (formerly $routeMiddleware).
     * You can use these by name in routes/groups.
     */
    protected $middlewareAliases = [
        'auth'             => \App\Http\Middleware\Authenticate::class,
        'auth.basic'       => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session'     => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'can'              => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'            => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed'           => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle'         => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified'         => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        // if you created it:
        'role'             => \App\Http\Middleware\EnsureRole::class,
    ];

}
