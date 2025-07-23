<?php

namespace App\Http;

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
        // Global middleware here
        'jwt.auth' => \App\Http\Middleware\JwtAuthMiddleware::class,
        'modify.request' => \App\Http\Middleware\ModifyRequestMiddleware::class,
    ];

     protected $routeMiddleware = [
        'check.permission' => \App\Http\Middleware\CheckPermissionMiddleware::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            // Web middleware group
        ],

        'api' => [
            // API middleware group
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    
}
