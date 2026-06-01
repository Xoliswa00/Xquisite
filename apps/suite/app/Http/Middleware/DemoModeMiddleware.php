<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DemoModeMiddleware
{
    // Routes that are blocked in demo mode (external side-effects)
    private const BLOCKED_ROUTES = [
        'pos.checkout',           // real payment flow
        'checkout.place',         // e-commerce checkout
        'payfast.*',              // payment gateway callbacks
        'sync.*',                 // billing sync queue
        'admin.tenants.store',    // creating real tenants
        'admin.tenants.destroy',  // deleting tenants
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->isDemo()) {
            return $next($request);
        }

        if ($this->isBlocked($request)) {
            $message = 'This action is disabled in demo mode. Create a free account to use the full platform.';

            if ($request->expectsJson()) {
                return response()->json(['error' => $message], 403);
            }

            return back()->with('demo_blocked', $message);
        }

        return $next($request);
    }

    private function isDemo(): bool
    {
        return auth()->check()
            && auth()->user()->tenant?->is_demo;
    }

    private function isBlocked(Request $request): bool
    {
        foreach (self::BLOCKED_ROUTES as $pattern) {
            if ($request->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }
}
