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
 *
 * That parameter is bound under two different route groups (see the
 * $registerShopRoutes closure in routes/web.php) — the value means a slug on
 * the /shop/{tenantSlug} path route, but a subdomain on the
 * {tenantSlug}.{app.domain} host route. Which column to match against comes
 * from the matched route's name, never guessed from the value itself — a
 * tenant's slug and a different tenant's subdomain are each validated
 * unique independently, not cross-checked against each other.
 */
class EnsureTenantModuleActive
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $identifier = $request->route('tenantSlug');
        $by = str_starts_with((string) $request->route()?->getName(), 'shop.host.') ? 'subdomain' : 'slug';

        $tenant = Tenant::activeForShop($identifier, $by);

        if (!$tenant || !$tenant->hasModule($module)) {
            abort(404);
        }

        return $next($request);
    }
}
