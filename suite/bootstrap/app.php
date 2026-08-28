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

// Resolves where an expired/guest session should be sent back to — the tenant's
// own portal login (with its slug) when the request was inside one, never the
// main staff login. Shared by the auth-guard redirect and the CSRF-expiry handler
// below so a page refresh or a stale form submit lands back on the same portal.
$portalLoginRedirect = function (\Illuminate\Http\Request $request): string {
    $slug = $request->route('slug');

    return match (true) {
        $slug && $request->is('rent/*')       => route('rent.login', $slug),
        $slug && $request->is('book/*')       => route('book.login', $slug),
        $slug && $request->is('contractor/*') => route('contractor.login', $slug),
        default                               => route('login'),
    };
};

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) use ($portalLoginRedirect): void {
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

        $middleware->redirectGuestsTo($portalLoginRedirect);

        $middleware->alias([
            'module' => EnsureModuleActive::class,
            'enforce-password-change' => EnforcePasswordChange::class,
            'company.suspension' => \App\Http\Middleware\CheckCompanySuspension::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) use ($portalLoginRedirect): void {
        // Last-resort net for any DB integrity violation that slips past
        // application-level validation (a race condition, a spot a validation
        // rule doesn't cover yet, etc.) — across every portal, not just the
        // ones we've explicitly hardened. Never let one of these reach the
        // user as a raw stack trace/500; the exception is still fully logged
        // to system_logs via the report() callback below regardless.
        $exceptions->render(function (\Illuminate\Database\UniqueConstraintViolationException $e, $request) {
            $message = 'That didn\'t save — this information (ID number, email, or phone number) already belongs to another record. Please check for a duplicate and try again.';

            return $request->expectsJson()
                ? response()->json(['message' => $message], 409)
                : back()->withInput()->withErrors(['error' => $message]);
        });

        $exceptions->render(function (\Illuminate\Database\QueryException $e, $request) {
            $message = 'That didn\'t save due to a data problem on our end. Please try again — if it keeps happening, let us know.';

            return $request->expectsJson()
                ? response()->json(['message' => $message], 500)
                : back()->withInput()->withErrors(['error' => $message]);
        });

        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) use ($portalLoginRedirect) {
            $message = 'Your session expired. Please sign in again.';

            // 'status' is what the main staff login checks (Breeze convention);
            // 'success' is what every portal layout (renter/booking/contractor) checks.
            // Flash both so the message shows up wherever this redirect lands.
            return redirect($portalLoginRedirect($request))
                ->with('status', $message)
                ->with('success', $message);
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
