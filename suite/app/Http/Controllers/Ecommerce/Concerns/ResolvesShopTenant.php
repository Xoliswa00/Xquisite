<?php

namespace App\Http\Controllers\Ecommerce\Concerns;

use App\Models\Tenant;
use Illuminate\Routing\Route;

/**
 * The public shop is reachable two ways — /shop/{tenantSlug} (always works)
 * and {tenantSlug}.{app.domain} (the tenant's own subdomain, once set) — see
 * routes/web.php's $registerShopRoutes closure. Both routes bind the same
 * {tenantSlug} parameter name so controller signatures don't have to change,
 * but the value means a different column depending on which one matched: a
 * literal slug on the path route, a subdomain on the host route. Which
 * column to query must come from the matched route's name, not be guessed
 * from the value — a tenant's slug and a different tenant's subdomain are
 * each validated unique independently, not cross-checked against each other.
 */
trait ResolvesShopTenant
{
    protected function activeShopTenant(string $identifier): Tenant
    {
        /** @var Route|null $route */
        $route = request()->route();
        $by = str_starts_with((string) $route?->getName(), 'shop.host.') ? 'subdomain' : 'slug';

        return Tenant::activeForShop($identifier, $by) ?? abort(404);
    }
}
