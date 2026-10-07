<?php

namespace App\Http\Middleware;

use App\Models\BlockedIp;
use App\Services\AuditService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckBlockedIp
{
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        if (! $ip || ! BlockedIp::isBlocked($ip)) {
            return $next($request);
        }

        $block = BlockedIp::active()->where('ip_address', $ip)->first();
        $until = $block?->expires_at;

        $message = "Access from your network (IP address {$ip}) has been blocked by the site administrator. "
            . ($until
                ? 'The block lifts ' . ($until->isToday() ? 'at ' . $until->format('H:i') : 'on ' . $until->format('j M Y \a\t H:i')) . '.'
                : 'It stays in place until an administrator removes it.')
            . ' If you think this is a mistake, contact support and quote this IP address.';

        $this->logRefusal($request, $ip);

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Access denied.', 'message' => $message], 403);
        }

        return response()->view('errors.ip-blocked', ['reason' => $message], 403);
    }

    // One entry per IP every five minutes: enough to see who was turned away
    // and where, without a blocked bot filling the audit log.
    private function logRefusal(Request $request, string $ip): void
    {
        if (! Cache::add("blocked_ip_refusal_logged:{$ip}", true, 300)) {
            return;
        }

        try {
            AuditService::log(
                action: 'security.blocked_ip_refused',
                entityType: 'BlockedIp',
                meta: ['method' => $request->method(), 'user_agent' => $request->userAgent()],
            );
        } catch (\Throwable) {
            // Never let a logging failure change what the visitor sees.
        }
    }
}
