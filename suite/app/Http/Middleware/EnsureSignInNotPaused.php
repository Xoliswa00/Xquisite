<?php

namespace App\Http\Middleware;

use App\Services\AuditService;
use App\Services\Security\LoginThrottleService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Refuses a sign-in or password-reset attempt while LoginThrottleService has
 * it paused, and says why and for how long. Applied to those form posts only.
 */
class EnsureSignInNotPaused
{
    public function handle(Request $request, Closure $next, string $channel = 'login'): Response
    {
        $email = $request->input('email');
        $email = is_string($email) ? $email : null;

        $lock = LoginThrottleService::lockedFor($request->ip(), $channel, $email);

        if (! $lock) {
            return $next($request);
        }

        $this->logRefusal($request, $channel, $lock['scope'], $email);

        $minutes = max(1, (int) ceil($lock['seconds'] / 60));
        $wait = $minutes === 1 ? 'about a minute' : "about {$minutes} minutes";
        $action = $channel === 'reset' ? 'Password resets' : 'Sign-in';

        $message = $lock['scope'] === 'ip'
            ? "There were too many failed attempts from your network, so signing in and resetting passwords from it is paused. You can try again in {$wait}. Anyone already signed in is not affected."
            : "{$action} for this email address is paused after " . LoginThrottleService::ACCOUNT_THRESHOLD . " failed attempts from your network. You can try again in {$wait}. Other accounts on your network are not affected.";

        if ($request->expectsJson()) {
            return response()
                ->json(['message' => $message, 'retry_after' => $lock['seconds']], 429)
                ->header('Retry-After', (string) $lock['seconds']);
        }

        return response()
            ->view('errors.sign-in-paused', ['reason' => $message], 429)
            ->header('Retry-After', (string) $lock['seconds']);
    }

    // One entry per pause, not one per retry, so a bot hammering the form
    // can't flood the audit log.
    private function logRefusal(Request $request, string $channel, string $scope, ?string $email): void
    {
        $marker = 'sign_in_refusal_logged:' . sha1($scope . '|' . $channel . '|' . $request->ip() . '|' . mb_strtolower((string) $email));

        if (! Cache::add($marker, true, LoginThrottleService::LOCK_SECONDS)) {
            return;
        }

        try {
            AuditService::log(
                action: 'auth.sign_in_refused',
                meta: ['scope' => $scope, 'channel' => $channel, 'email' => $email],
            );
        } catch (\Throwable) {
            // Never let a logging failure change what the visitor sees.
        }
    }
}
