<?php

namespace Matrix\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \Matrix\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \Matrix\Http\Middleware\TrustProxies::class,
        \Matrix\Http\Middleware\EnableCrossRequestMiddleware::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \Matrix\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Matrix\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Matrix\Http\Middleware\XJwtVerify::class,
        ],

        'api' => [
            \Illuminate\Session\Middleware\StartSession::class,
            'throttle:2000,1',
            'bindings',
        ],

        'client' => [
            // \Illuminate\Session\Middleware\StartSession::class,
            \Matrix\Http\Middleware\ClientMiddleware::class,
            'throttle:2000,1',
            'bindings',
        ],

        'interaction' => [
            // \Illuminate\Session\Middleware\StartSession::class,
            \Matrix\Http\Middleware\InteractionMiddleware::class,
            'throttle:2000,1',
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \Matrix\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'permission' => \Matrix\Http\Middleware\PermissionProtect::class,
        'cookiejudge' => \Matrix\Http\Middleware\CookieJudge::class,
        'sessionverify' => \Matrix\Http\Middleware\SessionVerify::class,
        'compatibleinterfacecookiejudge' => \Matrix\Http\Middleware\CompatibleInterfaceCookieJudge::class,
        'openapi' => \Matrix\Http\Middleware\OpenApiProtect::class,
    ];
}
