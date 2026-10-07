<?php

namespace App\Services\Security;

use App\Services\AuditService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Turns repeated failed sign-ins into a short pause on signing in.
 *
 * Two levels, both scoped to the sign-in and password-reset forms only (the
 * rest of the site, and anyone already signed in, is never affected):
 *
 *  - Account: 3 failures for one email from one IP pauses that email on that
 *    IP. Other people behind the same IP (a shop's Wi-Fi, a mobile carrier's
 *    shared address) keep working.
 *  - IP: 20 failures from one IP across any accounts pauses sign-in for the
 *    whole IP. This is the net for an attack that tries many accounts.
 *
 * Site-wide blocks are a separate, manual tool (BlockedIp + CheckBlockedIp).
 */
class LoginThrottleService
{
    public const ACCOUNT_THRESHOLD = 3;   // failures for one email from one IP
    public const IP_THRESHOLD = 20;       // failures from one IP, any account
    public const WINDOW_SECONDS = 900;    // 15 minutes
    public const LOCK_SECONDS = 300;      // 5 minutes

    public static function recordFailure(string $ip, string $context, ?string $identifier = null): void
    {
        $channel = self::channelFor($context);

        if ($identifier = self::normalise($identifier)) {
            $strikes = self::accountKey('strikes', $channel, $ip, $identifier);
            RateLimiter::hit($strikes, self::WINDOW_SECONDS);

            if (RateLimiter::attempts($strikes) >= self::ACCOUNT_THRESHOLD) {
                self::lock(self::accountKey('lock', $channel, $ip, $identifier));
                RateLimiter::clear($strikes);

                AuditService::log(
                    action: 'auth.sign_in_paused',
                    meta: ['scope' => 'account', 'channel' => $channel, 'context' => $context, 'email' => $identifier],
                );
            }
        }

        $strikes = "login_strikes:ip:{$ip}";
        RateLimiter::hit($strikes, self::WINDOW_SECONDS);

        if (RateLimiter::attempts($strikes) >= self::IP_THRESHOLD) {
            self::lock("login_lock:ip:{$ip}");
            RateLimiter::clear($strikes);

            AuditService::log(
                action: 'auth.sign_in_paused',
                meta: ['scope' => 'ip', 'context' => $context],
            );
        }
    }

    /**
     * A correct sign-in wipes that account's strikes. The IP-wide count is left
     * alone, or one valid account could be used to keep resetting it.
     */
    public static function recordSuccess(string $ip, ?string $identifier): void
    {
        if ($identifier = self::normalise($identifier)) {
            RateLimiter::clear(self::accountKey('strikes', 'login', $ip, $identifier));
        }
    }

    /**
     * @return array{scope: string, seconds: int}|null
     */
    public static function lockedFor(string $ip, string $channel, ?string $identifier): ?array
    {
        if ($seconds = self::secondsLeft("login_lock:ip:{$ip}")) {
            return ['scope' => 'ip', 'seconds' => $seconds];
        }

        if (($identifier = self::normalise($identifier))
            && ($seconds = self::secondsLeft(self::accountKey('lock', $channel, $ip, $identifier)))) {
            return ['scope' => 'account', 'seconds' => $seconds];
        }

        return null;
    }

    private static function lock(string $key): void
    {
        Cache::put($key, now()->addSeconds(self::LOCK_SECONDS)->getTimestamp(), self::LOCK_SECONDS);
    }

    private static function secondsLeft(string $key): int
    {
        return max(0, (int) Cache::get($key, 0) - now()->getTimestamp());
    }

    // Failed sign-ins must not also shut the "forgot password" form, which is
    // exactly where someone who keeps mistyping needs to go next.
    private static function channelFor(string $context): string
    {
        return str_ends_with($context, 'password-reset') ? 'reset' : 'login';
    }

    private static function normalise(?string $identifier): ?string
    {
        $identifier = mb_strtolower(trim((string) $identifier));

        return $identifier === '' ? null : $identifier;
    }

    private static function accountKey(string $kind, string $channel, string $ip, string $identifier): string
    {
        return "login_{$kind}:account:{$channel}:" . sha1($identifier . '|' . $ip);
    }
}
