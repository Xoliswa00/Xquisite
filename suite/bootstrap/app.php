<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\DemoModeMiddleware;
use App\Http\Middleware\EnsureModuleActive;
use App\Http\Middleware\RequestTrackingMiddleware;
use App\Http\Middleware\ResolveTenant;
use App\Http\Middleware\EnforcePasswordChange;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            RequestTrackingMiddleware::class,
            ResolveTenant::class,
            DemoModeMiddleware::class,
            \App\Http\Middleware\CheckCompanySuspension::class,
        ]);

        $middleware->alias([
            'module' => EnsureModuleActive::class,
            'enforce-password-change' => EnforcePasswordChange::class,
            'company.suspension' => \App\Http\Middleware\CheckCompanySuspension::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
