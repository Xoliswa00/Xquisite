<?php

namespace Tests\Feature\Pos;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\ProductVariant;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantAdminTest extends TestCase
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

    public function test_generating_options_creates_the_cartesian_product_of_variants(): void
    {
        $tenant  = Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
        $tenant->activateModule('pos');
        $manager = $this->manager($tenant);
        $product = Product::create(['tenant_id' => $tenant->id, 'name' => 'T-Shirt', 'price' => 200, 'is_active' => true]);

        $this->actingAs($manager)->post(route('products.variants.generate', $product), [
            'options' => [
                ['name' => 'Size', 'values' => 'S, M, L'],
                ['name' => 'Color', 'values' => 'Red, Blue'],
            ],
        ])->assertRedirect(route('products.variants.index', $product));

        $product->refresh();
        $this->assertTrue($product->has_variants);
        $this->assertSame(['Size' => ['S', 'M', 'L'], 'Color' => ['Red', 'Blue']], $product->variant_options);
        $this->assertSame(6, $product->variants()->count()); // 3 sizes × 2 colors
    }

    public function test_regenerating_with_a_narrower_value_list_never_deletes_existing_variants(): void
    {
        $tenant  = Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
        $tenant->activateModule('pos');
        $manager = $this->manager($tenant);
        $product = Product::create([
            'tenant_id' => $tenant->id, 'name' => 'T-Shirt', 'price' => 200, 'is_active' => true,
            'has_variants' => true, 'variant_options' => ['Size' => ['S', 'M']],
        ]);
        $existing = ProductVariant::create([
            'tenant_id' => $tenant->id, 'product_id' => $product->id,
            'attributes' => ['Size' => 'S'], 'stock_quantity' => 7, 'track_stock' => true, 'is_active' => true,
        ]);

        // Admin removes "S" from the option list and saves — the existing
        // Size:S variant (with real stock) must survive untouched.
        $this->actingAs($manager)->post(route('products.variants.generate', $product), [
            'options' => [['name' => 'Size', 'values' => 'M']],
        ])->assertRedirect();

        $this->assertSame(7, $existing->fresh()->stock_quantity);
        $this->assertNotNull(ProductVariant::find($existing->id));
    }

    public function test_generate_is_additive_and_never_duplicates_an_existing_combination(): void
    {
        $tenant  = Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
        $tenant->activateModule('pos');
        $manager = $this->manager($tenant);
        $product = Product::create([
            'tenant_id' => $tenant->id, 'name' => 'T-Shirt', 'price' => 200, 'is_active' => true,
            'has_variants' => true, 'variant_options' => ['Size' => ['S']],
        ]);
        ProductVariant::create([
            'tenant_id' => $tenant->id, 'product_id' => $product->id,
            'attributes' => ['Size' => 'S'], 'stock_quantity' => 4, 'track_stock' => true, 'is_active' => true,
        ]);

        $this->actingAs($manager)->post(route('products.variants.generate', $product), [
            'options' => [['name' => 'Size', 'values' => 'S, M']],
        ]);

        $this->assertSame(2, $product->variants()->count()); // S (kept, not duplicated) + new M
    }

    public function test_bulk_update_saves_stock_price_and_active_state_per_variant(): void
    {
        $tenant  = Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
        $tenant->activateModule('pos');
        $manager = $this->manager($tenant);
        $product = Product::create(['tenant_id' => $tenant->id, 'name' => 'T-Shirt', 'price' => 200, 'is_active' => true, 'has_variants' => true]);
        $variant = ProductVariant::create([
            'tenant_id' => $tenant->id, 'product_id' => $product->id,
            'attributes' => ['Size' => 'M'], 'stock_quantity' => 0, 'track_stock' => true, 'is_active' => true,
        ]);

        $this->actingAs($manager)->patch(route('products.variants.update', $product), [
            'variants' => [[
                'id' => $variant->id, 'sku' => 'TSH-M', 'price_override' => 250,
                'stock_quantity' => 20, 'track_stock' => 1, 'is_active' => 0,
            ]],
        ])->assertRedirect();

        $variant->refresh();
        $this->assertSame('TSH-M', $variant->sku);
        $this->assertSame(250.0, (float) $variant->price_override);
        $this->assertSame(20, $variant->stock_quantity);
        $this->assertFalse($variant->is_active);
    }

    public function test_bulk_update_links_a_variant_to_one_of_the_products_own_photos(): void
    {
        $tenant  = Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
        $tenant->activateModule('pos');
        $manager = $this->manager($tenant);
        $product = Product::create(['tenant_id' => $tenant->id, 'name' => 'T-Shirt', 'price' => 200, 'is_active' => true, 'has_variants' => true]);
        $photo   = $product->photos()->create(['tenant_id' => $tenant->id, 'path' => 'red.jpg', 'disk' => 'public']);
        $variant = ProductVariant::create([
            'tenant_id' => $tenant->id, 'product_id' => $product->id,
            'attributes' => ['Color' => 'Red'], 'stock_quantity' => 0, 'track_stock' => true, 'is_active' => true,
        ]);

        $this->actingAs($manager)->patch(route('products.variants.update', $product), [
            'variants' => [[
                'id' => $variant->id, 'stock_quantity' => 0, 'is_active' => 1,
                'product_photo_id' => $photo->id,
            ]],
        ])->assertRedirect();

        $this->assertSame($photo->id, $variant->fresh()->product_photo_id);
    }

    public function test_bulk_update_ignores_a_photo_id_belonging_to_a_different_product(): void
    {
        $tenant   = Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
        $tenant->activateModule('pos');
        $manager  = $this->manager($tenant);
        $product  = Product::create(['tenant_id' => $tenant->id, 'name' => 'T-Shirt', 'price' => 200, 'is_active' => true, 'has_variants' => true]);
        $other    = Product::create(['tenant_id' => $tenant->id, 'name' => 'Mug', 'price' => 50, 'is_active' => true]);
        $foreignPhoto = $other->photos()->create(['tenant_id' => $tenant->id, 'path' => 'mug.jpg', 'disk' => 'public']);
        $variant  = ProductVariant::create([
            'tenant_id' => $tenant->id, 'product_id' => $product->id,
            'attributes' => ['Color' => 'Red'], 'stock_quantity' => 0, 'track_stock' => true, 'is_active' => true,
        ]);

        $this->actingAs($manager)->patch(route('products.variants.update', $product), [
            'variants' => [[
                'id' => $variant->id, 'stock_quantity' => 0, 'is_active' => 1,
                'product_photo_id' => $foreignPhoto->id,
            ]],
        ])->assertRedirect();

        $this->assertNull($variant->fresh()->product_photo_id, 'A photo belonging to a different product must never be linkable.');
    }
}
