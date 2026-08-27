<?php

namespace App\Services\Security;

use App\Models\BlockedIp;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Escalates repeated failed logins from one IP — across any guard or account —
 * into an actual temporary block. Login throttling (LoginRequest's per-email
 * RateLimiter, and each portal's login_failed check) only ever delays retries;
 * nothing previously turned a sustained attempt into a real block.
 */
class LoginThrottleService
{
    private const THRESHOLD = 3;         // failed attempts, any account, within the window
    private const WINDOW_SECONDS = 900;  // 15 minutes
    private const BLOCK_MINUTES = 5;

    public static function recordFailure(string $ip, string $context): void
    {
        if (BlockedIp::isBlocked($ip)) {
            return;
        }

        $key = "auto_block:{$ip}";
        RateLimiter::hit($key, self::WINDOW_SECONDS);
        $attempts = RateLimiter::attempts($key);

        if ($attempts >= self::THRESHOLD) {
            BlockedIp::block(
                $ip,
                "Automated: {$attempts} failed login attempts ({$context}) — auto-releases in " . self::BLOCK_MINUTES . " min",
                null,
                self::BLOCK_MINUTES
            );

            RateLimiter::clear($key);
        }
    }
}
