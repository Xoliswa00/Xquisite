<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforcePasswordChange
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->needsPasswordChange()) {
            // Allow access to change-password view and POST request
            if ($request->routeIs('password.change') || $request->routeIs('password.update-first', 'POST')) {
                return $next($request);
            }

            // Allow logout
            if ($request->routeIs('logout')) {
                return $next($request);
            }

            // Redirect to change password
            return redirect()->route('password.change')->with('warning', 'You must change your password on first login.');
        }

        return $next($request);
    }
}
