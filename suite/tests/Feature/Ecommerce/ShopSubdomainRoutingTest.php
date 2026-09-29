<?php

namespace Tests\Feature\Ecommerce;

use App\Models\Tenant;
use App\Modules\POS\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A tenant's own subdomain ({subdomain}.{app.domain}) serves the exact same
 * storefront as /shop/{slug}, registered from the same $registerShopRoutes
 * closure in routes/web.php — see the comment there for why the domain-bound
 * half has to be registered before every other route in the file. This
 * suite pins that ordering down: a domain-less route (e.g. the marketing
 * homepage) matches ANY host, so if it were registered first it would
 * silently shadow the subdomain route on every tenant subdomain, and the
 * whole feature would never fire without a single test failing to say so.
 */
class ShopSubdomainRoutingTest extends TestCase
{
    use RefreshDatabase;

    private function domain(): string
    {
        return config('app.domain', 'xquisite.co.za');
    }

    private function tenant(array $overrides = []): Tenant
    {
        $tenant = Tenant::create(array_merge([
            'name'      => 'Test Store',
            'slug'      => 'test-store',
            'subdomain' => 'test-store',
            'is_active' => true,
        ], $overrides));
        $tenant->activateModule('ecommerce');

        return $tenant;
    }

    public function test_subdomain_serves_the_same_storefront_as_the_path_route(): void
    {
        $tenant = $this->tenant();
        Product::create([
            'tenant_id' => $tenant->id, 'name' => 'Widget', 'price' => 100,
            'stock_quantity' => 5, 'track_stock' => true, 'is_active' => true,
            'is_available_online' => true,
        ]);

        $this->get('http://test-store.' . $this->domain() . '/')
            ->assertOk()
            ->assertSee('Widget');
    }

    public function test_apex_domain_still_serves_the_marketing_homepage_not_shadowed(): void
    {
        // Sanity-checks the other half of the ordering fix: moving the
        // domain-bound group to the top of web.php must not make it start
        // swallowing requests to the plain apex host too — its domain regex
        // only matches an actual *.{app.domain} host. (The actual proof
        // that a domain-less route can't shadow the subdomain route the
        // *other* way is test_subdomain_serves_the_same_storefront_as_the_
        // path_route — it fails loudly if that ordering ever regresses,
        // since the marketing page would render instead of the storefront.)
        $this->tenant(); // a tenant with a subdomain exists, but isn't being visited

        $this->get('/')
            ->assertOk()
            ->assertSee('Xquisite');
    }

    public function test_a_tenant_with_no_subdomain_is_not_reachable_via_any_subdomain(): void
    {
        $tenant = $this->tenant(['subdomain' => null]);
        Product::create([
            'tenant_id' => $tenant->id, 'name' => 'Widget', 'price' => 100,
            'stock_quantity' => 5, 'track_stock' => true, 'is_active' => true,
            'is_available_online' => true,
        ]);

        // No subdomain set — guessing the slug as a subdomain must not work.
        $this->get('http://test-store.' . $this->domain() . '/')->assertNotFound();

        // The canonical path form still does.
        $this->get('/shop/test-store')->assertOk()->assertSee('Widget');
    }

    public function test_module_gate_applies_on_the_subdomain_route_too(): void
    {
        $tenant = $this->tenant();
        $tenant->deactivateModule('ecommerce');

        $this->get('http://test-store.' . $this->domain() . '/')->assertNotFound();
    }

    /**
     * A tenant's slug and a *different* tenant's subdomain are each
     * validated unique independently, not cross-checked against each other
     * — they could collide. Path routes must resolve by slug only, domain
     * routes by subdomain only, never either-or, or one tenant's storefront
     * could serve another tenant's products.
     */
    public function test_slug_and_subdomain_collision_resolves_to_the_right_tenant_per_route(): void
    {
        $tenantA = $this->tenant(['name' => 'Tenant A', 'slug' => 'shared', 'subdomain' => 'a-store']);
        $tenantB = $this->tenant(['name' => 'Tenant B', 'slug' => 'b-store', 'subdomain' => 'shared']);

        Product::create([
            'tenant_id' => $tenantA->id, 'name' => 'Product A', 'price' => 100,
            'stock_quantity' => 5, 'track_stock' => true, 'is_active' => true,
            'is_available_online' => true,
        ]);
        Product::create([
            'tenant_id' => $tenantB->id, 'name' => 'Product B', 'price' => 100,
            'stock_quantity' => 5, 'track_stock' => true, 'is_active' => true,
            'is_available_online' => true,
        ]);

        // /shop/shared must resolve Tenant A (slug='shared'), never Tenant B
        // even though Tenant B's *subdomain* happens to also be 'shared'.
        $this->get('/shop/shared')->assertOk()->assertSee('Product A')->assertDontSee('Product B');

        // shared.{domain} must resolve Tenant B (subdomain='shared'), never
        // Tenant A even though Tenant A's *slug* happens to also be 'shared'.
        $this->get('http://shared.' . $this->domain() . '/')
            ->assertOk()->assertSee('Product B')->assertDontSee('Product A');
    }

    /** Regression test for the cart-key fix that went with this routing change. */
    public function test_cart_is_the_same_tenant_whether_reached_by_path_or_subdomain(): void
    {
        $tenant = $this->tenant();
        $product = Product::create([
            'tenant_id' => $tenant->id, 'name' => 'Widget', 'price' => 100,
            'stock_quantity' => 5, 'track_stock' => true, 'is_active' => true,
            'is_available_online' => true,
        ]);

        // Add to cart via the path route...
        $this->post('/shop/test-store/cart/add', ['product_id' => $product->id, 'qty' => 1])
            ->assertRedirect();

        // ...and see it on the subdomain route, in the SAME test session.
        $this->get('http://test-store.' . $this->domain() . '/cart')
            ->assertOk()
            ->assertSee('Widget')
            ->assertSee('R100.00');
    }

    public function test_tenant_shop_route_prefers_subdomain_when_set(): void
    {
        $withSubdomain = $this->tenant(['slug' => 'with-sub', 'subdomain' => 'with-sub']);
        $withoutSubdomain = $this->tenant(['name' => 'No Sub', 'slug' => 'no-sub', 'subdomain' => null]);

        $this->assertSame(
            route('shop.host.index', ['tenantSlug' => 'with-sub']),
            $withSubdomain->shopRoute('index')
        );
        $this->assertSame(
            route('shop.index', ['tenantSlug' => 'no-sub']),
            $withoutSubdomain->shopRoute('index')
        );
    }
}
