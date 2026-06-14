<?php

use App\Http\Middleware\EnforceNoindex;
use App\Http\Middleware\SetLocaleMiddleware;
use App\Modules\Tenancy\Middleware\ResolveTenantFromSubdomain;
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
        $middleware->appendToGroup('web', ResolveTenantFromSubdomain::class);
        $middleware->appendToGroup('web', SetLocaleMiddleware::class);
        $middleware->append(EnforceNoindex::class);
        $middleware->alias([
            'resolve.tenant' => ResolveTenantFromSubdomain::class,
            'noindex' => EnforceNoindex::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
