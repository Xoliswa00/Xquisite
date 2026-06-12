<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCompanySuspension
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // System admins are never blocked
        if ($user->isSystemAdmin()) {
            return $next($request);
        }

        $company = $user->currentCompany;

        if ($company && $company->status === 'suspended') {
            // Allow access to billing pages so owner can pay
            if ($request->is('billing*') || $request->is('notifications*') || $request->is('logout')) {
                return $next($request);
            }

            return redirect()->route('billing.index')
                ->with('error', 'Your account has been suspended due to non-payment. Please pay your outstanding invoice to restore access.');
        }

        return $next($request);
    }
}
