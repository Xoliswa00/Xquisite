<?php

namespace App\Http\Middleware;

use App\Services\AuditService;
use App\Services\Security\LoginThrottleService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Refuses a sign-in or password-reset attempt while LoginThrottleService has
 * it paused, and says why and for how long. Applied to those form posts only,
 * ahead of `throttle:auth` so retries against a paused account don't use up
 * the per-IP allowance that everyone else on the network shares.
 */
class EnsureSignInNotPaused
{
    public function handle(Request $request, Closure $next, string $guard, string $channel): Response
    {
        $email = $request->input('email');
        $email = is_string($email) ? Str::limit(trim($email), 190, '') : null;

        $lock = LoginThrottleService::lockedFor($request->ip(), $guard, $channel, $email);

        if (! $lock) {
            return $next($request);
        }

        $this->logRefusal($request, $guard, $channel, $lock['scope'], $email);

        $message = $this->message($lock['scope'], $channel, $lock['seconds'], $email);

        if ($request->expectsJson()) {
            return response()
                ->json(['message' => $message, 'retry_after' => $lock['seconds']], 429)
                ->header('Retry-After', (string) $lock['seconds']);
        }

        return response()
            ->view('errors.sign-in-paused', [
                'reason' => $message,
                'signInUrl' => $this->portalUrl($request, $guard, 'login'),
                // Resets stay open during a sign-in pause for one account.
                'resetUrl' => $lock['scope'] === 'account' && $channel === 'login'
                    ? $this->portalUrl($request, $guard, 'password.request')
                    : null,
            ], 429)
            ->header('Retry-After', (string) $lock['seconds']);
    }

    private function message(string $scope, string $channel, int $seconds, ?string $email): string
    {
        $minutes = max(1, (int) ceil($seconds / 60));
        $wait = $minutes === 1 ? 'about a minute' : "about {$minutes} minutes";
        $who = $email ?: 'this email address';
        $tries = LoginThrottleService::ACCOUNT_THRESHOLD;

        return match (true) {
            $scope === 'ip' => "There have been too many failed sign-in attempts from your internet connection, so signing in and resetting passwords is paused. You can try again in {$wait}. Anyone already signed in can carry on working.",
            $channel === 'reset' => "There were {$tries} password reset attempts for {$who} that did not work, so resets for that email are paused. You can try again in {$wait}. Check that this is the email address you signed up with.",
            default => "There were {$tries} failed sign-in attempts for {$who}, so sign-in for that email is paused. You can try again in {$wait}. Trying sooner will not add to the wait.",
        };
    }

    // Back to the same portal the person was on, never the marketing home page.
    private function portalUrl(Request $request, string $guard, string $name): string
    {
        $slug = $request->route('slug');

        return match (true) {
            $guard === 'customer' && $slug => route("book.{$name}", $slug),
            $guard === 'renter' && $slug => route("rent.{$name}", $slug),
            $guard === 'contractor' && $slug => route("contractor.{$name}", $slug),
            default => route($name),
        };
    }

    // One entry per pause, not one per retry. An IP-wide pause is logged once
    // for the IP whatever email is submitted, so changing it can't flood the log.
    private function logRefusal(Request $request, string $guard, string $channel, string $scope, ?string $email): void
    {
        $subject = $scope === 'ip' ? '' : Str::lower((string) $email);
        $marker = 'sign_in_refusal_logged:' . sha1("{$scope}|{$guard}|{$channel}|{$request->ip()}|{$subject}");

        if (! Cache::add($marker, true, LoginThrottleService::LOCK_SECONDS)) {
            return;
        }

        try {
            AuditService::log(
                action: 'auth.sign_in_refused',
                meta: array_filter([
                    'scope' => $scope,
                    'guard' => $guard,
                    'channel' => $channel,
                    'email' => $scope === 'ip' ? null : $email,
                    'tenant_slug' => $request->route('slug'),
                ]),
            );
        } catch (\Throwable) {
            // Never let a logging failure change what the visitor sees.
        }
    }
}
