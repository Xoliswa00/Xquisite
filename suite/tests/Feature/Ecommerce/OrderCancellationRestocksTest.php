<?php

namespace Tests\Feature\Ecommerce;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Ecommerce\Models\Order;
use App\Modules\Ecommerce\Services\OrderService;
use App\Modules\POS\Models\Product;
use App\Services\Cart\CartService;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * OrderController::updateStatus previously did a bare $order->update() —
 * cancelling an order from the admin UI left its reserved stock stranded as
 * sold forever, since releaseInventory() only ran from the PayFast-IPN-
 * failure path and the stale-order expiry command, never from here.
 */
class OrderCancellationRestocksTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionRoleSeeder::class);
    }

    private function place(Tenant $tenant, Product $product): Order
    {
        $cart = new CartService($tenant->id);
        $cart->add($product->id, 1);

        return app(OrderService::class)->placeOrder($tenant, [
            'customer_name'    => 'Jane Doe',
            'customer_email'   => 'jane@example.com',
            'fulfillment_type' => 'collection',
            'payment_method'   => 'eft',
        ], $cart, 'test-key-' . uniqid());
    }

    public function test_admin_cancelling_an_order_releases_its_reserved_stock(): void
    {
        $tenant = Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
        $tenant->activateModule('ecommerce');
        $manager = User::factory()->create(['tenant_id' => $tenant->id]);
        $manager->assignRole('manager');

        $product = Product::create([
            'tenant_id' => $tenant->id, 'name' => 'Widget', 'price' => 100,
            'stock_quantity' => 5, 'track_stock' => true, 'is_active' => true,
            'is_available_online' => true,
        ]);

        $order = $this->place($tenant, $product);
        $this->assertSame(4, $product->fresh()->stock_quantity);

        $this->actingAs($manager)
            ->patch("/orders/{$order->id}/status", ['status' => 'cancelled'])
            ->assertRedirect();

        $this->assertSame(5, $product->fresh()->stock_quantity);
        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_cancelling_an_already_cancelled_order_does_not_double_release_stock(): void
    {
        $tenant = Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
        $tenant->activateModule('ecommerce');
        $manager = User::factory()->create(['tenant_id' => $tenant->id]);
        $manager->assignRole('manager');

        $product = Product::create([
            'tenant_id' => $tenant->id, 'name' => 'Widget', 'price' => 100,
            'stock_quantity' => 5, 'track_stock' => true, 'is_active' => true,
            'is_available_online' => true,
        ]);

        $order = $this->place($tenant, $product);

        $this->actingAs($manager)->patch("/orders/{$order->id}/status", ['status' => 'cancelled']);
        $this->assertSame(5, $product->fresh()->stock_quantity);

        // Re-submitting the same status (e.g. a stale tab, a double click)
        // must not restock a second time.
        $this->actingAs($manager)->patch("/orders/{$order->id}/status", ['status' => 'cancelled']);
        $this->assertSame(5, $product->fresh()->stock_quantity);
    }

    public function test_marking_an_order_refunded_does_not_release_stock(): void
    {
        $tenant = Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
        $tenant->activateModule('ecommerce');
        $manager = User::factory()->create(['tenant_id' => $tenant->id]);
        $manager->assignRole('manager');

        $product = Product::create([
            'tenant_id' => $tenant->id, 'name' => 'Widget', 'price' => 100,
            'stock_quantity' => 5, 'track_stock' => true, 'is_active' => true,
            'is_available_online' => true,
        ]);

        $order = $this->place($tenant, $product);
        $this->assertSame(4, $product->fresh()->stock_quantity);

        // Refund follows fulfillment (goods already left) — it's a separate
        // returns decision, not an automatic restock.
        $this->actingAs($manager)->patch("/orders/{$order->id}/status", ['status' => 'refunded']);
        $this->assertSame(4, $product->fresh()->stock_quantity);
    }
}
