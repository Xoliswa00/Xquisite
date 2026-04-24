<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;


class ResolveTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

       $tenantId = null;

        // 1. From authenticated user (primary)
        if (Auth::check()) {
            $tenantId = Auth::user()->tenant_id;
        }

        // 2. Future: subdomain support
        if (!$tenantId && $request->getHost()) {
            // e.g. tenant1.app.com
            $subdomain = explode('.', $request->getHost())[0];

            // placeholder for future lookup:
            // $tenantId = Tenant::where('slug', $subdomain)->value('id');
        }

        // 3. API fallback (optional)
        if (!$tenantId && $request->header('X-Tenant-ID')) {
            $tenantId = (int) $request->header('X-Tenant-ID');
        }

        if ($tenantId) {
            TenantContext::set($tenantId);
        }
        return $next($request);
    }
}
