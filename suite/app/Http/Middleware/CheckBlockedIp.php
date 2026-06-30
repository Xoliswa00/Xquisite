<?php

namespace App\Http\Middleware;

use App\Models\BlockedIp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBlockedIp
{
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        if ($ip && BlockedIp::isBlocked($ip)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Access denied.'], 403);
            }

            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
