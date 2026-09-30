<?php

namespace Tests\Feature\Ecommerce;

use App\Models\Promotion;
use App\Models\ServiceCombo;
use App\Models\Tenant;
use App\Modules\Ecommerce\Models\Order;
use App\Modules\Ecommerce\Models\OrderItem;
use App\Modules\POS\Models\Product;
use App\Services\Cart\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ComboCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(): Tenant
    {
        $tenant = Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
        $tenant->activateModule('ecommerce');

        return $tenant;
    }

    private function product(Tenant $tenant, array $overrides = []): Product
    {
        return Product::create(array_merge([
            'tenant_id' => $tenant->id, 'is_active' => true, 'is_available_online' => true,
            'track_stock' => true, 'stock_quantity' => 10,
        ], $overrides));
    }

    private function combo(Tenant $tenant, array $products, array $overrides = []): ServiceCombo
    {
        $combo = ServiceCombo::create(array_merge([
            'tenant_id' => $tenant->id, 'name' => 'Starter Bundle',
            'discount_type' => 'percentage', 'discount_value' => 10, 'is_active' => true,
        ], $overrides));
        $combo->products()->sync(collect($products)->pluck('id'));

        return $combo;
    }

    private function checkoutPayload(array $overrides = []): array
    {
        return array_merge([
            'customer_name'    => 'Jane Doe',
            'customer_email'   => 'jane@example.com',
            'fulfillment_type' => 'collection',
            'payment_method'   => 'eft',
        ], $overrides);
    }

    public function test_combo_appears_on_the_storefront_combos_page(): void
    {
        $tenant = $this->tenant();
        $mug    = $this->product($tenant, ['name' => 'Mug', 'price' => 50]);
        $this->combo($tenant, [$mug]);

        $response = $this->get(route('shop.combos', 'test-store'));

        $response->assertOk();
        $response->assertSee('Starter Bundle');
        $this->assertSame(1, $response->viewData('combos')->count());
    }

    public function test_a_combo_with_no_products_never_appears_on_the_storefront(): void
    {
        $tenant = $this->tenant();
        ServiceCombo::create([
            'tenant_id' => $tenant->id, 'name' => 'Empty', 'discount_type' => 'percentage',
            'discount_value' => 10, 'is_active' => true,
        ]);

        $response = $this->get(route('shop.combos', 'test-store'));

        $this->assertSame(0, $response->viewData('combos')->count());
    }

    public function test_a_combo_with_a_deactivated_product_is_hidden_from_the_storefront(): void
    {
        $tenant = $this->tenant();
        $mug    = $this->product($tenant, ['name' => 'Mug', 'price' => 50, 'is_active' => false]);
        $this->combo($tenant, [$mug]);

        $response = $this->get(route('shop.combos', 'test-store'));

        $this->assertSame(0, $response->viewData('combos')->count());
    }

    public function test_adding_a_combo_to_cart_stores_it(): void
    {
        $tenant = $this->tenant();
        $mug    = $this->product($tenant, ['name' => 'Mug', 'price' => 50]);
        $combo  = $this->combo($tenant, [$mug]);

        $response = $this->post(route('shop.cart.combo.add', 'test-store'), ['combo_id' => $combo->id, 'qty' => 1]);

        $response->assertRedirect();
        $response->assertSessionHas('cart_success');
        $this->assertSame(1, session('cart_combo.' . $tenant->id)[$combo->id]);
    }

    public function test_adding_a_combo_removes_an_already_applied_promo_code(): void
    {
        $tenant = $this->tenant();
        $mug    = $this->product($tenant, ['name' => 'Mug', 'price' => 50]);
        $combo  = $this->combo($tenant, [$mug]);
        $cart   = new CartService($tenant->id);
        $cart->setPromoCode('SOMECODE');

        $this->withSession(['cart_promo.' . $tenant->id => 'SOMECODE'])
            ->post(route('shop.cart.combo.add', 'test-store'), ['combo_id' => $combo->id, 'qty' => 1]);

        $this->assertNull(session('cart_promo.' . $tenant->id));
    }

    public function test_applying_a_promo_is_rejected_while_a_combo_is_in_the_cart(): void
    {
        $tenant = $this->tenant();

        $response = $this->withSession(['cart_combo.' . $tenant->id => [1 => 1]])
            ->post(route('shop.cart.promo.apply', 'test-store'), ['code' => 'ANYTHING']);

        $response->assertSessionHas('cart_error');
        $this->assertNull(session('cart_promo.' . $tenant->id));
    }

    public function test_checkout_expands_a_combo_into_one_order_item_per_product_with_prorated_pricing(): void
    {
        Mail::fake();
        $tenant  = $this->tenant();
        $mug     = $this->product($tenant, ['name' => 'Mug', 'price' => 30]);
        $coaster = $this->product($tenant, ['name' => 'Coaster', 'price' => 20]);
        $combo   = $this->combo($tenant, [$mug, $coaster], ['discount_type' => 'percentage', 'discount_value' => 10]);
        // total 50, 10% off = combo_price 45. Pro-rated: mug 30/50*45=27, coaster 20/50*45=18. 27+18=45 exactly.

        $session = ['cart_combo.' . $tenant->id => [$combo->id => 1]];

        $response = $this->withSession($session)->post(route('shop.checkout.place', 'test-store'), $this->checkoutPayload());

        $order = Order::first();
        $response->assertRedirect(route('shop.order.confirmed', ['test-store', $order->reference]));

        $this->assertSame(2, $order->items()->count());
        $this->assertEquals(45.0, (float) $order->subtotal);
        $this->assertEquals(45.0, (float) $order->total);

        $mugItem = $order->items()->where('product_id', $mug->id)->first();
        $coasterItem = $order->items()->where('product_id', $coaster->id)->first();
        $this->assertEquals(27.0, (float) $mugItem->unit_price);
        $this->assertEquals(18.0, (float) $coasterItem->unit_price);
        $this->assertSame($combo->id, $mugItem->combo_id);
        $this->assertSame('Starter Bundle', $mugItem->combo_name);
        $this->assertSame($combo->id, $coasterItem->combo_id);
    }

    public function test_checkout_decrements_stock_for_every_product_in_the_combo(): void
    {
        Mail::fake();
        $tenant  = $this->tenant();
        $mug     = $this->product($tenant, ['name' => 'Mug', 'price' => 30, 'stock_quantity' => 5]);
        $coaster = $this->product($tenant, ['name' => 'Coaster', 'price' => 20, 'stock_quantity' => 5]);
        $combo   = $this->combo($tenant, [$mug, $coaster]);

        $session = ['cart_combo.' . $tenant->id => [$combo->id => 2]]; // 2 bundles

        $this->withSession($session)->post(route('shop.checkout.place', 'test-store'), $this->checkoutPayload());

        $this->assertSame(3, $mug->fresh()->stock_quantity);     // 5 - 2
        $this->assertSame(3, $coaster->fresh()->stock_quantity); // 5 - 2
    }

    public function test_oversell_on_one_combo_product_rejects_the_whole_bundle_purchase(): void
    {
        $tenant  = $this->tenant();
        $mug     = $this->product($tenant, ['name' => 'Mug', 'price' => 30, 'stock_quantity' => 1]);
        $coaster = $this->product($tenant, ['name' => 'Coaster', 'price' => 20, 'stock_quantity' => 10]);
        $combo   = $this->combo($tenant, [$mug, $coaster]);

        // Ask for 2 bundles — mug only has 1 in stock.
        $session = ['cart_combo.' . $tenant->id => [$combo->id => 2]];

        $response = $this->withSession($session)->post(route('shop.checkout.place', 'test-store'), $this->checkoutPayload());

        $response->assertRedirect(route('shop.cart', 'test-store'));
        $this->assertSame(0, Order::count());
        $this->assertSame(1, $mug->fresh()->stock_quantity, 'Stock must roll back untouched.');
        $this->assertSame(10, $coaster->fresh()->stock_quantity, 'Stock must roll back untouched.');
    }

    public function test_checkout_with_both_a_plain_product_and_a_combo_in_the_cart(): void
    {
        Mail::fake();
        $tenant  = $this->tenant();
        $socks   = $this->product($tenant, ['name' => 'Socks', 'price' => 25]);
        $mug     = $this->product($tenant, ['name' => 'Mug', 'price' => 30]);
        $coaster = $this->product($tenant, ['name' => 'Coaster', 'price' => 20]);
        $combo   = $this->combo($tenant, [$mug, $coaster], ['discount_type' => 'percentage', 'discount_value' => 10]);

        $session = [
            'cart.' . $tenant->id       => ['p' . $socks->id => ['product_id' => $socks->id, 'variant_id' => null, 'qty' => 1]],
            'cart_combo.' . $tenant->id => [$combo->id => 1],
        ];

        $response = $this->withSession($session)->post(route('shop.checkout.place', 'test-store'), $this->checkoutPayload());

        $order = Order::first();
        $response->assertRedirect(route('shop.order.confirmed', ['test-store', $order->reference]));

        // 25 (socks, full price) + 45 (combo, already discounted) = 70.
        $this->assertSame(3, $order->items()->count());
        $this->assertEquals(70.0, (float) $order->total);
        $this->assertNull($order->items()->where('product_id', $socks->id)->first()->combo_id);
    }

    public function test_a_deactivated_combo_product_fails_checkout_cleanly(): void
    {
        $tenant  = $this->tenant();
        $mug     = $this->product($tenant, ['name' => 'Mug', 'price' => 30]);
        $coaster = $this->product($tenant, ['name' => 'Coaster', 'price' => 20]);
        $combo   = $this->combo($tenant, [$mug, $coaster]);

        // Deactivated after being added to the combo — checkout must
        // re-validate live, not trust what applied at add-to-cart time.
        $coaster->update(['is_active' => false]);

        $session = ['cart_combo.' . $tenant->id => [$combo->id => 1]];

        $response = $this->withSession($session)->post(route('shop.checkout.place', 'test-store'), $this->checkoutPayload());

        $response->assertRedirect(route('shop.cart', 'test-store'));
        $this->assertSame(0, Order::count());
    }

    public function test_cancelling_a_combo_order_restocks_every_product(): void
    {
        Mail::fake();
        $tenant  = $this->tenant();
        $mug     = $this->product($tenant, ['name' => 'Mug', 'price' => 30, 'stock_quantity' => 5]);
        $coaster = $this->product($tenant, ['name' => 'Coaster', 'price' => 20, 'stock_quantity' => 5]);
        $combo   = $this->combo($tenant, [$mug, $coaster]);

        $session = ['cart_combo.' . $tenant->id => [$combo->id => 1]];
        $this->withSession($session)->post(route('shop.checkout.place', 'test-store'), $this->checkoutPayload());

        $this->assertSame(4, $mug->fresh()->stock_quantity);
        $this->assertSame(4, $coaster->fresh()->stock_quantity);

        $order = Order::first();
        app(\App\Modules\Ecommerce\Services\OrderService::class)->releaseInventory($order);

        $this->assertSame(5, $mug->fresh()->stock_quantity);
        $this->assertSame(5, $coaster->fresh()->stock_quantity);
    }
}
