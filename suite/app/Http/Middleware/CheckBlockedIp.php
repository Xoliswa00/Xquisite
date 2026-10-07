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

        $until = BlockedIp::active()->where('ip_address', $ip)->value('expires_at');
        $until = $until ? \Illuminate\Support\Carbon::parse($until) : null;

        $message = 'Xquisite Creations has blocked access from your internet connection. '
            . ($until
                ? 'The block lifts ' . ($until->isToday() ? 'at ' . $until->format('H:i') . ' today' : 'on ' . $until->format('j M Y \a\t H:i')) . '.'
                : 'It stays in place until we remove it.');

        $this->logRefusal($request, $ip);

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Access denied.', 'message' => $message, 'reference' => $ip], 403);
        }

        return response()->view('errors.ip-blocked', [
            'reason' => $message,
            'ip' => $ip,
            'supportEmail' => config('contact.support_email'),
        ], 403);
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
