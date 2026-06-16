<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCompanySuspension
{
    private const ALLOWED_PREFIXES = ['billing', 'notifications', 'logout'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // No authenticated staff user — customers and guests pass straight through
        if (!$user || !($user instanceof User)) {
            return $next($request);
        }

        // System admins bypass suspension checks
        if ($user->isSystemAdmin()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName() ?? '';

        // Allow billing, notifications, and logout routes through
        foreach (self::ALLOWED_PREFIXES as $prefix) {
            if (str_starts_with($routeName, $prefix) || str_starts_with($request->path(), $prefix)) {
                return $next($request);
            }
        }

        $tenant = $user->tenant;
        if ($tenant && $tenant->suspended_at) {
            return redirect()->route('billing.index')
                ->with('error', 'Your account has been suspended due to non-payment. Please settle your outstanding invoice to restore access.');
        }

        return $next($request);
    }
}
