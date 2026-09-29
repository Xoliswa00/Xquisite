<?php

namespace Tests\Feature\Pos;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\ProductVariant;
use App\Modules\POS\Models\Sale;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Ringing up a variant product at the till decrements that specific
 * variant's stock, not the parent product's — same underlying contract as
 * the online checkout (ProductVariantCheckoutTest), exercised through the
 * separate POS Sale/SaleItem pipeline instead of the ecommerce Order one.
 */
class ProductVariantSaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionRoleSeeder::class);
    }

    private function manager(Tenant $tenant): User
    {
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $user->assignRole('manager');

        return $user;
    }

    public function test_pos_checkout_decrements_the_specific_variant_sold(): void
    {
        $tenant = Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
        $tenant->activateModule('pos');
        $manager = $this->manager($tenant);

        $product = Product::create([
            'tenant_id' => $tenant->id, 'name' => 'T-Shirt', 'price' => 200,
            'stock_quantity' => 0, 'track_stock' => true, 'is_active' => true,
            'has_variants' => true, 'variant_options' => ['Size' => ['M', 'L']],
        ]);
        $medium = ProductVariant::create([
            'tenant_id' => $tenant->id, 'product_id' => $product->id,
            'attributes' => ['Size' => 'M'], 'stock_quantity' => 5, 'track_stock' => true, 'is_active' => true,
        ]);
        $large = ProductVariant::create([
            'tenant_id' => $tenant->id, 'product_id' => $product->id,
            'attributes' => ['Size' => 'L'], 'stock_quantity' => 5, 'track_stock' => true, 'is_active' => true,
        ]);

        $this->actingAs($manager)->post('/pos/checkout', [
            'items' => [[
                'type' => 'product', 'id' => $product->id, 'variant_id' => $medium->id,
                'name' => 'T-Shirt (Size: M)', 'price' => 200, 'qty' => 2,
            ]],
            'payment_method' => 'cash',
        ])->assertRedirect();

        $this->assertSame(1, Sale::count());
        $item = Sale::first()->items->first();
        $this->assertSame($medium->id, $item->product_variant_id);
        $this->assertSame(['Size' => 'M'], $item->variant_attributes);

        $this->assertSame(3, $medium->fresh()->stock_quantity);
        $this->assertSame(5, $large->fresh()->stock_quantity, 'A different variant of the same product must be untouched.');
        $this->assertSame(0, $product->fresh()->stock_quantity, 'The parent product stock_quantity is not authoritative once it has variants.');
    }
}
