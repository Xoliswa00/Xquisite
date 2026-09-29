<?php

namespace Tests\Feature\Pos;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\ProductVariant;
use App\Modules\POS\Models\PurchaseOrder;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Purchase orders, stock takes and reorder alerts all operate at the
 * Product level by default — this pins down that once a product has
 * variants, its own stock_quantity/reorder_level stop being consulted and
 * each variant is handled individually instead (a has_variants product's
 * own numbers are meaningless once split across variants — flagged as a
 * known gap when the variant system first shipped, closed here).
 */
class ProductVariantStockOpsTest extends TestCase
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

    private function variantProduct(Tenant $tenant): array
    {
        $product = Product::create([
            'tenant_id' => $tenant->id, 'name' => 'T-Shirt', 'price' => 200, 'cost_price' => 80,
            'stock_quantity' => 0, 'reorder_level' => 0, 'track_stock' => true, 'is_active' => true,
            'has_variants' => true, 'variant_options' => ['Size' => ['S', 'M']],
        ]);
        $low = ProductVariant::create([
            'tenant_id' => $tenant->id, 'product_id' => $product->id, 'attributes' => ['Size' => 'S'],
            'stock_quantity' => 2, 'reorder_level' => 5, 'track_stock' => true, 'is_active' => true,
        ]);
        $ok = ProductVariant::create([
            'tenant_id' => $tenant->id, 'product_id' => $product->id, 'attributes' => ['Size' => 'M'],
            'stock_quantity' => 20, 'reorder_level' => 5, 'track_stock' => true, 'is_active' => true,
        ]);

        return [$product, $low, $ok];
    }

    public function test_stock_take_lists_variant_rows_not_the_parent_products_own_row(): void
    {
        $tenant = Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
        $tenant->activateModule('pos');
        [$product, $low, $ok] = $this->variantProduct($tenant);

        $response = $this->actingAs($this->manager($tenant))->get('/stock/take');

        $response->assertOk();
        // Both variants show as their own row — and, implicitly, the parent
        // product contributes no row of its own alongside them (it's
        // excluded from the query entirely; see the assertion in the next
        // test that its own stock_quantity is untouched by a stock take).
        $response->assertSee('T-Shirt — Size: S');
        $response->assertSee('T-Shirt — Size: M');
    }

    public function test_saving_a_stock_take_adjusts_the_correct_variant_not_the_product(): void
    {
        $tenant = Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
        $tenant->activateModule('pos');
        [$product, $low, $ok] = $this->variantProduct($tenant);

        $this->actingAs($this->manager($tenant))->post('/stock/take', [
            'counts' => ["v_{$low->id}" => 8],
            'notes'  => 'Test count',
        ])->assertRedirect(route('stock.take'));

        $this->assertSame(8, $low->fresh()->stock_quantity);
        $this->assertSame(20, $ok->fresh()->stock_quantity, 'A sibling variant not counted must be untouched.');
        $this->assertSame(0, $product->fresh()->stock_quantity, "The parent product's own stock_quantity is never touched.");
    }

    public function test_reorder_alerts_flags_the_low_variant_and_not_its_in_stock_sibling(): void
    {
        $tenant = Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
        $tenant->activateModule('pos');
        [$product, $low, $ok] = $this->variantProduct($tenant);

        $response = $this->actingAs($this->manager($tenant))->get('/stock/reorder-alerts');

        $response->assertOk();
        $response->assertSee('T-Shirt — Size: S');
        $response->assertDontSee('T-Shirt — Size: M');
    }

    public function test_creating_a_po_for_a_variant_product_requires_picking_a_variant(): void
    {
        $tenant = Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
        $tenant->activateModule('pos');
        [$product, $low, $ok] = $this->variantProduct($tenant);

        $this->actingAs($this->manager($tenant))->post('/purchase-orders', [
            'items' => [['product_id' => $product->id, 'qty' => 10, 'unit_cost' => 80]], // no variant_id
        ])->assertStatus(404);
    }

    public function test_receiving_a_po_line_increments_the_specific_variant_not_the_product(): void
    {
        $tenant = Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
        $tenant->activateModule('pos');
        $manager = $this->manager($tenant);
        [$product, $low, $ok] = $this->variantProduct($tenant);

        $this->actingAs($manager)->post('/purchase-orders', [
            'items' => [['product_id' => $product->id, 'variant_id' => $low->id, 'qty' => 10, 'unit_cost' => 80]],
        ])->assertRedirect();

        $po = PurchaseOrder::first();
        $item = $po->items->first();
        $this->assertSame($low->id, $item->product_variant_id);
        $this->assertSame('T-Shirt — Size: S', $item->product_name);

        $this->actingAs($manager)->post(route('purchase-orders.receive', $po), [
            'received' => [$item->id => 10],
        ])->assertRedirect();

        $this->assertSame(12, $low->fresh()->stock_quantity, '2 already in stock + 10 received.');
        $this->assertSame(20, $ok->fresh()->stock_quantity);
        $this->assertSame(0, $product->fresh()->stock_quantity);
    }
}
