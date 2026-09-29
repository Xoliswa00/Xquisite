<?php

namespace Tests\Feature\Ecommerce;

use App\Models\Tenant;
use App\Modules\POS\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The public /shop/{tenantSlug} routes have no logged-in user to gate on, so
 * deactivating the ecommerce module must 404 the storefront directly (via
 * EnsureTenantModuleActive, keyed on the route's tenantSlug) rather than
 * relying on the staff-side module:ecommerce checks, which only cover the
 * admin orders/settings pages and leave the public shop reachable.
 */
class StorefrontModuleGateTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(): Tenant
    {
        return Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
    }

    public function test_storefront_404s_when_ecommerce_module_is_inactive(): void
    {
        $tenant = $this->tenant();
        // No activateModule('ecommerce') call — module stays inactive.
        Product::create([
            'tenant_id' => $tenant->id, 'name' => 'Widget', 'price' => 100,
            'stock_quantity' => 5, 'track_stock' => true, 'is_active' => true,
            'is_available_online' => true,
        ]);

        $this->get('/shop/test-store')->assertNotFound();
        $this->get('/shop/test-store/cart')->assertNotFound();
    }

    public function test_storefront_serves_once_ecommerce_module_is_active(): void
    {
        $tenant = $this->tenant();
        $tenant->activateModule('ecommerce');
        Product::create([
            'tenant_id' => $tenant->id, 'name' => 'Widget', 'price' => 100,
            'stock_quantity' => 5, 'track_stock' => true, 'is_active' => true,
            'is_available_online' => true,
        ]);

        $this->get('/shop/test-store')->assertOk()->assertSee('Widget');
    }

    public function test_storefront_404s_once_ecommerce_module_is_deactivated_again(): void
    {
        $tenant = $this->tenant();
        $tenant->activateModule('ecommerce');
        $this->get('/shop/test-store')->assertOk();

        $tenant->deactivateModule('ecommerce');

        $this->get('/shop/test-store')->assertNotFound();
    }
}
