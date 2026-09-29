<?php

namespace Tests\Feature\Ecommerce;

use App\Models\Tenant;
use App\Modules\Ecommerce\Models\Order;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * A variant product (Size×Color etc.) sells like a normal product from the
 * shopper's side, but stock/price live on the ProductVariant row, not the
 * parent Product — this pins down that the whole chain (cart -> checkout ->
 * order -> stock decrement) actually targets the variant, not the product,
 * and that it stays correctly isolated per-variant (buying a Red Medium
 * must never touch a Blue Medium's stock).
 */
class ProductVariantCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(): Tenant
    {
        $tenant = Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
        $tenant->activateModule('ecommerce');

        return $tenant;
    }

    private function variantProduct(Tenant $tenant): Product
    {
        $product = Product::create([
            'tenant_id'           => $tenant->id,
            'name'                => 'T-Shirt',
            'price'               => 200, // fallback only — every variant here overrides it
            'stock_quantity'      => 0,   // not authoritative once has_variants is true
            'track_stock'         => true,
            'is_active'           => true,
            'is_available_online' => true,
            'has_variants'        => true,
            'variant_options'     => ['Size' => ['S', 'M'], 'Color' => ['Red', 'Blue']],
        ]);

        foreach ([['Size' => 'S', 'Color' => 'Red'], ['Size' => 'M', 'Color' => 'Red'], ['Size' => 'M', 'Color' => 'Blue']] as $attrs) {
            ProductVariant::create([
                'tenant_id'      => $tenant->id,
                'product_id'     => $product->id,
                'attributes'     => $attrs,
                'price_override' => $attrs['Color'] === 'Blue' ? 220 : null, // Blue costs more
                'stock_quantity' => 3,
                'track_stock'    => true,
                'is_active'      => true,
            ]);
        }

        return $product;
    }

    private function checkoutPayload(): array
    {
        return [
            'customer_name'    => 'Jane Doe',
            'customer_email'   => 'jane@example.com',
            'fulfillment_type' => 'collection',
            'payment_method'   => 'eft',
        ];
    }

    public function test_variant_add_to_cart_requires_a_valid_variant_id(): void
    {
        $tenant  = $this->tenant();
        $product = $this->variantProduct($tenant);

        $this->post(route('shop.cart.add', 'test-store'), ['product_id' => $product->id])
            ->assertStatus(404); // firstOrFail — no variant_id supplied for a variant product
    }

    public function test_checkout_decrements_only_the_purchased_variant_not_the_product_or_sibling_variants(): void
    {
        Mail::fake();
        $tenant  = $this->tenant();
        $product = $this->variantProduct($tenant);
        $mediumRed  = $product->variants()->where('attributes->Size', 'M')->where('attributes->Color', 'Red')->first();
        $mediumBlue = $product->variants()->where('attributes->Size', 'M')->where('attributes->Color', 'Blue')->first();

        $this->post(route('shop.cart.add', 'test-store'), [
            'product_id' => $product->id,
            'variant_id' => $mediumRed->id,
            'qty'        => 2,
        ])->assertRedirect();

        $response = $this->post(route('shop.checkout.place', 'test-store'), $this->checkoutPayload());

        $this->assertSame(1, Order::count());
        $order = Order::first();
        $response->assertRedirect(route('shop.order.confirmed', ['test-store', $order->reference]));

        // Purchased variant decremented...
        $this->assertSame(1, $mediumRed->fresh()->stock_quantity);
        // ...its sibling (same size, different color) untouched...
        $this->assertSame(3, $mediumBlue->fresh()->stock_quantity);
        // ...and the parent product's own stock_quantity (not authoritative once has_variants) untouched.
        $this->assertSame(0, $product->fresh()->stock_quantity);

        $item = $order->items->first();
        $this->assertSame($mediumRed->id, $item->product_variant_id);
        $this->assertSame(['Size' => 'M', 'Color' => 'Red'], $item->variant_attributes);
        $this->assertSame('T-Shirt — Size: M, Color: Red', $item->product_name);
        // Base product price (200) ignored — the Red Medium has no override, still correct at 200.
        $this->assertSame(400.0, (float) $order->subtotal);
    }

    public function test_variant_price_override_is_used_not_the_base_product_price(): void
    {
        Mail::fake();
        $tenant  = $this->tenant();
        $product = $this->variantProduct($tenant);
        $mediumBlue = $product->variants()->where('attributes->Size', 'M')->where('attributes->Color', 'Blue')->first();

        $this->post(route('shop.cart.add', 'test-store'), [
            'product_id' => $product->id,
            'variant_id' => $mediumBlue->id,
            'qty'        => 1,
        ]);
        $this->post(route('shop.checkout.place', 'test-store'), $this->checkoutPayload());

        $order = Order::first();
        // Blue's 220 override, not the product's base 200.
        $this->assertSame(220.0, (float) $order->subtotal);
    }

    public function test_oversell_on_a_specific_variant_is_rejected_without_touching_other_variants(): void
    {
        $tenant  = $this->tenant();
        $product = $this->variantProduct($tenant);
        $small = $product->variants()->where('attributes->Size', 'S')->first();

        $this->post(route('shop.cart.add', 'test-store'), [
            'product_id' => $product->id,
            'variant_id' => $small->id,
            'qty'        => 3, // exactly at stock — allowed
        ]);

        // Tamper the session to ask for more than is in stock, same pattern
        // CheckoutTest uses for the non-variant oversell case.
        $this->withSession(['cart.' . $tenant->id => [
            'v' . $small->id => ['product_id' => $product->id, 'variant_id' => $small->id, 'qty' => 5],
        ]])->post(route('shop.checkout.place', 'test-store'), $this->checkoutPayload())
            ->assertRedirect(route('shop.cart', 'test-store'));

        $this->assertSame(0, Order::count());
        $this->assertSame(3, $small->fresh()->stock_quantity, 'Stock must be untouched on rollback.');
    }
}
