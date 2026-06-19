<?php

namespace Tests\Feature\Ecommerce;

use App\Models\Tenant;
use App\Modules\Ecommerce\Models\Order;
use App\Modules\Ecommerce\Services\OrderService;
use App\Modules\POS\Models\Product;
use App\Services\Cart\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(): Tenant
    {
        return Tenant::create([
            'name'      => 'Test Store',
            'slug'      => 'test-store',
            'is_active' => true,
        ]);
    }

    private function product(Tenant $tenant, array $overrides = []): Product
    {
        return Product::create(array_merge([
            'tenant_id'           => $tenant->id,
            'name'                => 'Widget',
            'price'               => 100,
            'stock_quantity'      => 5,
            'track_stock'         => true,
            'is_active'           => true,
            'is_available_online' => true,
        ], $overrides));
    }

    private function checkoutPayload(array $overrides = []): array
    {
        return array_merge([
            'customer_name'    => 'Jane Doe',
            'customer_email'   => 'jane@example.com',
            'fulfillment_type' => 'collection',
            'payment_method'   => 'eft',
            'idempotency_key'  => 'irrelevant-server-uses-session',
        ], $overrides);
    }

    public function test_eft_checkout_creates_one_order_and_decrements_stock(): void
    {
        Mail::fake();
        $tenant  = $this->tenant();
        $product = $this->product($tenant, ['stock_quantity' => 5]);

        $response = $this->withSession(['cart.test-store' => [$product->id => 2]])
            ->post(route('shop.checkout.place', 'test-store'), $this->checkoutPayload());

        $this->assertSame(1, Order::count());
        $order = Order::first();
        $response->assertRedirect(route('shop.order.confirmed', ['test-store', $order->reference]));

        $this->assertStringStartsWith('ORD-', $order->reference);
        $this->assertSame(200.0, (float) $order->total);
        $this->assertSame(3, $product->fresh()->stock_quantity);
    }

    public function test_oversell_is_rejected_and_rolls_back(): void
    {
        $tenant  = $this->tenant();
        $product = $this->product($tenant, ['stock_quantity' => 1]);

        // Cart asks for more than is in stock (e.g. tampered/stale session).
        $response = $this->withSession(['cart.test-store' => [$product->id => 2]])
            ->post(route('shop.checkout.place', 'test-store'), $this->checkoutPayload());

        $response->assertRedirect(route('shop.cart', 'test-store'));
        $response->assertSessionHas('error');

        $this->assertSame(0, Order::count());
        $this->assertSame(1, $product->fresh()->stock_quantity, 'Stock must be untouched on rollback.');
    }

    public function test_duplicate_submission_with_same_key_creates_one_order(): void
    {
        Mail::fake();
        $tenant  = $this->tenant();
        $product = $this->product($tenant, ['stock_quantity' => 10]);
        $service = app(OrderService::class);

        $cart = new CartService('test-store');
        $cart->add($product->id, 1);

        $data = [
            'customer_name'    => 'Jane',
            'customer_email'   => 'jane@example.com',
            'fulfillment_type' => 'collection',
            'payment_method'   => 'eft',
        ];

        $first  = $service->placeOrder($tenant, $data, $cart, 'fixed-key-123');
        $second = $service->placeOrder($tenant, $data, $cart, 'fixed-key-123');

        $this->assertSame(1, Order::count());
        $this->assertSame($first->id, $second->id);
        $this->assertTrue($first->wasRecentlyCreated);
        $this->assertFalse($second->wasRecentlyCreated, 'Replay must not create a new order.');
        // Stock only decremented once.
        $this->assertSame(9, $product->fresh()->stock_quantity);
    }

    public function test_empty_cart_redirects_without_creating_order(): void
    {
        $this->tenant();

        $response = $this->post(route('shop.checkout.place', 'test-store'), $this->checkoutPayload());

        $response->assertRedirect(route('shop.index', 'test-store'));
        $this->assertSame(0, Order::count());
    }
}
