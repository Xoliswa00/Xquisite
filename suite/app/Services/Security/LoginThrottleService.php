<?php

namespace App\Services\Security;

use App\Services\AuditService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use InvalidArgumentException;

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
 *
 * Every sign-in, forgot-password and reset-password POST needs both halves:
 * the `sign-in.pause:{guard},{channel}` route middleware (refuses while
 * paused) and a recordFailure() call (earns the pause).
 * SignInPauseTest::test_every_sign_in_and_reset_post_is_guarded checks the
 * first half.
 */
class LoginThrottleService
{
    public const ACCOUNT_THRESHOLD = 3;   // failures for one email from one IP
    public const IP_THRESHOLD = 20;       // failures from one IP, any account
    public const WINDOW_SECONDS = 900;    // 15 minutes
    public const LOCK_SECONDS = 300;      // 5 minutes

    public const GUARDS = ['staff', 'customer', 'renter', 'contractor'];

    // Failed sign-ins must not also shut the "forgot password" form, which is
    // exactly where someone who keeps mistyping needs to go next.
    public const CHANNELS = ['login', 'reset'];

    /**
     * @return int|null Attempts left before this account is paused (0 = it is
     *                  paused now), or null when there was no email to count.
     */
    public static function recordFailure(string $ip, string $guard, string $channel, ?string $identifier, array $meta = []): ?int
    {
        self::assertKnown($guard, $channel);

        $left = null;

        if ($identifier = self::normalise($identifier)) {
            $strikes = self::accountKey('strikes', $guard, $channel, $ip, $identifier);
            $attempts = RateLimiter::hit($strikes, self::WINDOW_SECONDS);
            $left = max(0, self::ACCOUNT_THRESHOLD - $attempts);

            if ($left === 0) {
                self::lock(self::accountKey('lock', $guard, $channel, $ip, $identifier));
                RateLimiter::clear($strikes);

                AuditService::log(
                    action: 'auth.sign_in_paused',
                    meta: ['scope' => 'account', 'guard' => $guard, 'channel' => $channel, 'email' => Str::limit($identifier, 190, '')] + $meta,
                );
            }
        }

        $strikes = "login_strikes:ip:{$ip}";

        if (RateLimiter::hit($strikes, self::WINDOW_SECONDS) >= self::IP_THRESHOLD) {
            self::lock("login_lock:ip:{$ip}");
            RateLimiter::clear($strikes);

            AuditService::log(
                action: 'auth.sign_in_paused',
                meta: ['scope' => 'ip', 'guard' => $guard, 'channel' => $channel] + $meta,
            );
        }

        return $left;
    }

    /**
     * A correct sign-in wipes that account's strikes. The IP-wide count is left
     * alone, or one valid account could be used to keep resetting it.
     */
    public static function recordSuccess(string $ip, string $guard, ?string $identifier): void
    {
        self::assertKnown($guard, 'login');

        if ($identifier = self::normalise($identifier)) {
            RateLimiter::clear(self::accountKey('strikes', $guard, 'login', $ip, $identifier));
        }
    }

    /**
     * @return array{scope: string, seconds: int}|null
     */
    public static function lockedFor(string $ip, string $guard, string $channel, ?string $identifier): ?array
    {
        self::assertKnown($guard, $channel);

        if ($seconds = self::secondsLeft("login_lock:ip:{$ip}")) {
            return ['scope' => 'ip', 'seconds' => $seconds];
        }

        if (($identifier = self::normalise($identifier))
            && ($seconds = self::secondsLeft(self::accountKey('lock', $guard, $channel, $ip, $identifier)))) {
            return ['scope' => 'account', 'seconds' => $seconds];
        }

        return null;
    }

    /**
     * What to add to the "wrong details" message so the pause never arrives
     * unannounced. Says the same thing whether or not the account exists.
     */
    public static function warning(?int $attemptsLeft): string
    {
        $minutes = (int) ceil(self::LOCK_SECONDS / 60);

        return match ($attemptsLeft) {
            1 => " One more failed attempt will pause sign-in for this email for {$minutes} minutes.",
            0 => " Sign-in for this email is now paused for {$minutes} minutes. If you have forgotten your password, you can reset it in the meantime.",
            default => '',
        };
    }

    private static function lock(string $key): void
    {
        Cache::put($key, now()->addSeconds(self::LOCK_SECONDS)->getTimestamp(), self::LOCK_SECONDS);
    }

    private static function secondsLeft(string $key): int
    {
        return max(0, (int) Cache::get($key, 0) - now()->getTimestamp());
    }

    // A typo in a guard or channel would otherwise read or write a key that
    // nothing else uses, and the pause would silently never apply.
    private static function assertKnown(string $guard, string $channel): void
    {
        if (! in_array($guard, self::GUARDS, true) || ! in_array($channel, self::CHANNELS, true)) {
            throw new InvalidArgumentException("Unknown sign-in pause guard/channel [{$guard}, {$channel}].");
        }
    }

    // Transliterated because the users tables compare emails accent-insensitively,
    // so "ádmin@" reaches the same account as "admin@" and must share its strikes.
    private static function normalise(?string $identifier): ?string
    {
        $identifier = Str::lower(Str::transliterate(trim((string) $identifier)));

        return $identifier === '' ? null : $identifier;
    }

    // The guard is part of the key so signing in to one portal can't clear (or
    // trip) the strikes for the same email on another.
    private static function accountKey(string $kind, string $guard, string $channel, string $ip, string $identifier): string
    {
        return "login_{$kind}:account:{$guard}:{$channel}:" . sha1($identifier . '|' . $ip);
    }
}
