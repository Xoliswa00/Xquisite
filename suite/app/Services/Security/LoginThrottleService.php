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
 * An IP that earns a second IP-wide pause within an hour gets a 30-minute pause
 * instead of 5, still on the sign-in forms only.
 *
 * Nothing here ever creates a site-wide block. BlockedIp + CheckBlockedIp is a
 * manual tool for administrators: an automatic site-wide block could be set off
 * on purpose by anyone sharing a shop's Wi-Fi, to shut its till and bookings out.
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

    // A second IP-wide pause inside this window is a sustained attack, not typos,
    // so it lasts longer. It is still a sign-in pause, never a site-wide block.
    public const ESCALATION_PAUSES = 2;
    public const ESCALATION_WINDOW_SECONDS = 3600;
    public const ESCALATED_LOCK_SECONDS = 1800;   // 30 minutes

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
        $email = Str::lower(trim((string) $identifier));

        if ($identifier = self::normalise($identifier)) {
            $strikes = self::accountKey('strikes', $guard, $channel, $ip, $identifier);
            $attempts = RateLimiter::hit($strikes, self::WINDOW_SECONDS);
            $left = max(0, self::ACCOUNT_THRESHOLD - $attempts);

            if ($left === 0) {
                self::lock(self::accountKey('lock', $guard, $channel, $ip, $identifier));
                RateLimiter::clear($strikes);

                self::audit(['scope' => 'account', 'guard' => $guard, 'channel' => $channel, 'email' => Str::limit($identifier, 190, '')] + $meta);

                if ($channel === 'login') {
                    try {
                        SignInPauseNotifier::accountPaused($guard, $email, $meta['tenant_slug'] ?? null);
                    } catch (\Throwable $e) {
                        // Telling people is a courtesy; it must never break the sign-in form.
                        report($e);
                    }
                }
            }
        }

        $strikes = "login_strikes:ip:{$ip}";

        if (RateLimiter::hit($strikes, self::WINDOW_SECONDS) >= self::IP_THRESHOLD) {
            $repeat  = RateLimiter::hit("login_ip_pauses:{$ip}", self::ESCALATION_WINDOW_SECONDS) >= self::ESCALATION_PAUSES;
            $seconds = $repeat ? self::ESCALATED_LOCK_SECONDS : self::LOCK_SECONDS;

            self::lock("login_lock:ip:{$ip}", $seconds);
            RateLimiter::clear($strikes);

            self::audit(['scope' => 'ip', 'guard' => $guard, 'channel' => $channel, 'minutes' => (int) ceil($seconds / 60)] + $meta);

            if ($repeat) {
                try {
                    SignInPauseNotifier::networkPausedAgain($ip, (int) ceil($seconds / 60));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
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
     * Seconds left on one specific pause (0 when it has ended or been lifted).
     * Unlike lockedFor(), an account pause is not masked by an IP-wide one.
     */
    public static function pauseSecondsLeft(string $scope, string $ip, ?string $guard = null, ?string $channel = null, ?string $identifier = null): int
    {
        return ($key = self::lockKey($scope, $ip, $guard, $channel, $identifier)) ? self::secondsLeft($key) : 0;
    }

    /**
     * Lift a pause early (admin action). For an account pause the strikes go
     * too, or the very next typo would pause it again.
     */
    public static function unlock(string $scope, string $ip, ?string $guard = null, ?string $channel = null, ?string $identifier = null): bool
    {
        if (! ($key = self::lockKey($scope, $ip, $guard, $channel, $identifier))) {
            return false;
        }

        Cache::forget($key);

        if ($scope === 'ip') {
            // An admin has looked at this address and let it back in, so its
            // next pause starts from scratch, not as a repeat offender.
            RateLimiter::clear("login_strikes:ip:{$ip}");
            RateLimiter::clear("login_ip_pauses:{$ip}");
        } else {
            RateLimiter::clear(self::accountKey('strikes', $guard, $channel, $ip, self::normalise($identifier)));
        }

        return true;
    }

    private static function lockKey(string $scope, string $ip, ?string $guard, ?string $channel, ?string $identifier): ?string
    {
        if ($scope === 'ip') {
            return "login_lock:ip:{$ip}";
        }

        $identifier = self::normalise($identifier);

        if ($scope !== 'account' || ! $identifier
            || ! in_array($guard, self::GUARDS, true) || ! in_array($channel, self::CHANNELS, true)) {
            return null;
        }

        return self::accountKey('lock', $guard, $channel, $ip, $identifier);
    }

    /**
     * What to add to the "wrong details" message so the pause never arrives
     * unannounced. Says the same thing whether or not the account exists.
     */
    public static function warning(?int $attemptsLeft): string
    {
        $minutes = (int) ceil(self::LOCK_SECONDS / 60);

        return match ($attemptsLeft) {
            1 => " One more failed attempt will pause sign-in for this login for {$minutes} minutes.",
            0 => " Sign-in for this login is now paused for {$minutes} minutes. If you have forgotten your password, you can reset it in the meantime.",
            default => '',
        };
    }

    // The pause is already in place by the time this runs. A failed audit write
    // must not turn a wrong password into an error page.
    private static function audit(array $meta): void
    {
        try {
            AuditService::log(action: 'auth.sign_in_paused', meta: $meta);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private static function lock(string $key, int $seconds = self::LOCK_SECONDS): void
    {
        Cache::put($key, now()->addSeconds($seconds)->getTimestamp(), $seconds);
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
