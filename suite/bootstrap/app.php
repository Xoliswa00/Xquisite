<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckBlockedIp;
use App\Http\Middleware\DemoModeMiddleware;
use App\Http\Middleware\EnsureModuleActive;
use App\Http\Middleware\RequestTrackingMiddleware;
use App\Http\Middleware\ResolveTenant;
use App\Http\Middleware\EnforcePasswordChange;
use App\Http\Middleware\SecurityHeaders;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(prepend: [
            CheckBlockedIp::class,
        ]);

        $middleware->web(append: [
            RequestTrackingMiddleware::class,
            ResolveTenant::class,
            DemoModeMiddleware::class,
            \App\Http\Middleware\CheckCompanySuspension::class,
            SecurityHeaders::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            '/js-error',
        ]);

        $middleware->alias([
            'module' => EnsureModuleActive::class,
            'enforce-password-change' => EnforcePasswordChange::class,
            'company.suspension' => \App\Http\Middleware\CheckCompanySuspension::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            return redirect()->route('login')->with('status', 'Your session expired. Please sign in again.');
        });

        // Log every exception to the database, including 404s, 403s, and handled errors
        $exceptions->report(function (\Throwable $e) {
            try {
                $path   = request()->path();
                $source = match (true) {
                    str_starts_with($path, 'book/')   => 'booking-portal',
                    str_starts_with($path, 'admin/')  => 'admin',
                    str_starts_with($path, 'portal/') => 'client-portal',
                    str_starts_with($path, 'shop/')   => 'shop',
                    default                           => 'suite',
                };

                $level = match (true) {
                    $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException       => 'WARNING',
                    $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException               => 'WARNING',
                    $e instanceof \Illuminate\Auth\AuthenticationException                            => 'INFO',
                    $e instanceof \Illuminate\Validation\ValidationException                         => 'INFO',
                    $e instanceof \Illuminate\Session\TokenMismatchException                         => 'INFO',
                    default                                                                          => 'ERROR',
                };

                \Illuminate\Support\Facades\DB::table('system_logs')->insert([
                    'level'      => $level,
                    'message'    => get_class($e) . ': ' . $e->getMessage(),
                    'file'       => $e->getFile(),
                    'line'       => $e->getLine(),
                    'context'    => json_encode([
                        'exception'  => get_class($e),
                        'referrer'   => request()->header('Referer'),
                        'user_agent' => request()->header('User-Agent'),
                        'session_id' => session()->getId(),
                        'location'   => \App\Support\IpLocation::get(request()->ip()),
                    ]),
                    'request_id' => app()->bound('request_id') ? app('request_id') : null,
                    'user_id'    => auth()->id(),
                    'ip_address' => request()->ip(),
                    'url'        => request()->fullUrl(),
                    'status'     => 'new',
                    'source'     => $source,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return false; // DB write succeeded — suppress duplicate file logging
            } catch (\Throwable) {
                // DB logging failed — fall through so Laravel's file logger still captures it
            }
        });
    })->create();
