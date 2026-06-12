<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
      $user = $request->user();

        // Check if the user has a current company set
        if (! $user || ! $user->current_company_id) {
            abort(403, 'No active company selected.');
        }

        // Optional: verify user is actually part of that company
        if (! $user->companies()->where('company_id', $user->current_company_id)->exists()) {
            abort(403, 'You do not belong to this company.');
        }

        return $next($request);    }
}
