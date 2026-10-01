<?php

namespace Tests\Unit;

use App\Models\Tenant;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSwatchColorsTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(): Tenant
    {
        return Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
    }

    private function variant(Product $product, array $attributes, array $overrides = []): ProductVariant
    {
        return ProductVariant::create(array_merge([
            'tenant_id'  => $product->tenant_id,
            'product_id' => $product->id,
            'attributes' => $attributes,
            'is_active'  => true,
        ], $overrides));
    }

    public function test_returns_distinct_color_values_across_active_variants(): void
    {
        $tenant = $this->tenant();
        $product = Product::create([
            'tenant_id' => $tenant->id, 'name' => 'Tee', 'price' => 100, 'is_active' => true,
            'has_variants' => true, 'variant_options' => ['Size' => ['S', 'M'], 'Color' => ['Red', 'Blue']],
        ]);

        $this->variant($product, ['Size' => 'S', 'Color' => 'Red']);
        $this->variant($product, ['Size' => 'M', 'Color' => 'Red']); // duplicate color, must not repeat
        $this->variant($product, ['Size' => 'S', 'Color' => 'Blue']);

        $this->assertSame(['Red', 'Blue'], $product->swatchColors());
    }

    public function test_matches_the_color_axis_case_insensitively(): void
    {
        $tenant = $this->tenant();
        $product = Product::create([
            'tenant_id' => $tenant->id, 'name' => 'Tee', 'price' => 100, 'is_active' => true,
            'has_variants' => true, 'variant_options' => ['Size' => ['S'], 'colour' => ['Green']],
        ]);

        $this->variant($product, ['Size' => 'S', 'colour' => 'Green']);

        $this->assertSame(['Green'], $product->swatchColors());
    }

    public function test_ignores_an_inactive_variant(): void
    {
        $tenant = $this->tenant();
        $product = Product::create([
            'tenant_id' => $tenant->id, 'name' => 'Tee', 'price' => 100, 'is_active' => true,
            'has_variants' => true, 'variant_options' => ['Color' => ['Red']],
        ]);

        $this->variant($product, ['Color' => 'Red'], ['is_active' => false]);

        $this->assertSame([], $product->swatchColors());
    }

    public function test_returns_empty_for_a_plain_product(): void
    {
        $tenant = $this->tenant();
        $product = Product::create(['tenant_id' => $tenant->id, 'name' => 'Mug', 'price' => 50, 'is_active' => true]);

        $this->assertSame([], $product->swatchColors());
    }

    public function test_returns_empty_when_there_is_no_color_like_axis(): void
    {
        $tenant = $this->tenant();
        $product = Product::create([
            'tenant_id' => $tenant->id, 'name' => 'Tee', 'price' => 100, 'is_active' => true,
            'has_variants' => true, 'variant_options' => ['Size' => ['S', 'M']],
        ]);

        $this->variant($product, ['Size' => 'S']);

        $this->assertSame([], $product->swatchColors());
    }
}
