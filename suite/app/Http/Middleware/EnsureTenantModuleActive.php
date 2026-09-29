<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for public, unauthenticated tenant-scoped routes (e.g. the online
 * storefront) — there is no logged-in user to check, so the tenant is
 * resolved from the route's {tenantSlug} parameter instead of auth(), and
 * an inactive/unlicensed module 404s like an unknown store rather than
 * redirecting to a login-only route (see EnsureModuleActive for that case).
 */
class EnsureTenantModuleActive
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $tenantSlug = $request->route('tenantSlug');

        $tenant = Tenant::where('slug', $tenantSlug)->where('is_active', true)->first();

        if (!$tenant || !$tenant->hasModule($module)) {
            abort(404);
        }

        return $next($request);
    }
}
