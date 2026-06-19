<?php

namespace Tests\Feature\Ecommerce;

use App\Models\Tenant;
use App\Modules\Ecommerce\Exceptions\InsufficientStockException;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private function product(int $stock): Product
    {
        $tenant = Tenant::create(['name' => 'S', 'slug' => 's', 'is_active' => true]);

        return Product::create([
            'tenant_id'      => $tenant->id,
            'name'           => 'Widget',
            'price'          => 10,
            'stock_quantity' => $stock,
            'track_stock'    => true,
            'is_active'      => true,
        ]);
    }

    public function test_lenient_decrement_floors_at_zero_and_never_goes_negative(): void
    {
        $product = $this->product(2);

        app(InventoryService::class)->decrement($product, 5);

        $this->assertSame(0, $product->fresh()->stock_quantity);
    }

    public function test_reserve_throws_when_insufficient_and_leaves_stock_untouched(): void
    {
        $product = $this->product(1);

        $this->expectException(InsufficientStockException::class);

        try {
            app(InventoryService::class)->reserve($product, 3, 'ORD-TEST');
        } finally {
            $this->assertSame(1, $product->fresh()->stock_quantity);
        }
    }

    public function test_reserve_decrements_exactly_when_sufficient(): void
    {
        $product = $this->product(10);

        app(InventoryService::class)->reserve($product, 4, 'ORD-TEST');

        $this->assertSame(6, $product->fresh()->stock_quantity);
    }
}
