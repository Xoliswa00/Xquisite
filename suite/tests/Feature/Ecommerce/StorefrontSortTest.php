<?php

namespace Tests\Feature\Ecommerce;

use App\Models\Tenant;
use App\Modules\POS\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontSortTest extends TestCase
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
        ], $overrides));
    }

    public function test_price_ascending_sort_orders_products_by_price(): void
    {
        $tenant = $this->tenant();
        $this->product($tenant, ['name' => 'Expensive', 'price' => 300]);
        $this->product($tenant, ['name' => 'Cheap', 'price' => 50]);
        $this->product($tenant, ['name' => 'Mid', 'price' => 150]);

        $response = $this->get(route('shop.index', 'test-store') . '?sort=price_asc');

        $names = $response->viewData('products')->pluck('name')->all();
        $this->assertSame(['Cheap', 'Mid', 'Expensive'], $names);
    }

    public function test_price_descending_sort_orders_products_by_price(): void
    {
        $tenant = $this->tenant();
        $this->product($tenant, ['name' => 'Expensive', 'price' => 300]);
        $this->product($tenant, ['name' => 'Cheap', 'price' => 50]);

        $response = $this->get(route('shop.index', 'test-store') . '?sort=price_desc');

        $names = $response->viewData('products')->pluck('name')->all();
        $this->assertSame(['Expensive', 'Cheap'], $names);
    }

    public function test_default_sort_is_unchanged_category_then_name(): void
    {
        $tenant = $this->tenant();
        $this->product($tenant, ['name' => 'Zebra', 'category' => 'A', 'price' => 10]);
        $this->product($tenant, ['name' => 'Apple', 'category' => 'A', 'price' => 20]);

        $response = $this->get(route('shop.index', 'test-store'));

        $names = $response->viewData('products')->pluck('name')->all();
        $this->assertSame(['Apple', 'Zebra'], $names);
    }
}
