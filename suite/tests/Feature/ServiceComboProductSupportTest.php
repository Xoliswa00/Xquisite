<?php

namespace Tests\Feature;

use App\Models\ServiceCombo;
use App\Models\ServiceCategory;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Booking\Models\Service;
use App\Modules\POS\Models\Product;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the restored product support on combos — combo_items originally
 * had a product_id column, a later migration dropped it in favour of
 * services-only, this feature brings it back (mixed service/product
 * combos, via combo_items again). See ServiceCombo::products()/services().
 */
class ServiceComboProductSupportTest extends TestCase
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

    private function tenant(): Tenant
    {
        $tenant = Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
        $tenant->activateModule('pos');
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

    private function service(Tenant $tenant, array $overrides = []): Service
    {
        $category = ServiceCategory::create(['tenant_id' => $tenant->id, 'name' => 'Cat', 'icon' => 'x']);

        return Service::create(array_merge([
            'tenant_id' => $tenant->id, 'duration_minutes' => 30, 'is_active' => true,
            'service_category_id' => $category->id,
        ], $overrides));
    }

    public function test_creating_a_combo_with_only_products(): void
    {
        $tenant  = $this->tenant();
        $manager = $this->manager($tenant);
        $mug     = $this->product($tenant, ['name' => 'Mug', 'price' => 50]);
        $coaster = $this->product($tenant, ['name' => 'Coaster', 'price' => 20]);

        $this->actingAs($manager)->post(route('combos.store'), [
            'name' => 'Mug + Coaster', 'discount_type' => 'percentage', 'discount_value' => 10,
            'is_active' => 1, 'product_ids' => [$mug->id, $coaster->id],
        ])->assertRedirect(route('services.index', ['tab' => 'combos']));

        $combo = ServiceCombo::first();
        $this->assertNotNull($combo);
        $this->assertSame(2, $combo->products->count());
        $this->assertSame(0, $combo->services->count());
        $this->assertEquals(70.0, $combo->total_price);
        $this->assertEquals(63.0, $combo->combo_price);
    }

    public function test_creating_a_mixed_combo_with_a_service_and_a_product(): void
    {
        $tenant  = $this->tenant();
        $manager = $this->manager($tenant);
        $mug     = $this->product($tenant, ['name' => 'Mug', 'price' => 50]);
        $haircut = $this->service($tenant, ['name' => 'Haircut', 'price' => 100]);

        $this->actingAs($manager)->post(route('combos.store'), [
            'name' => 'Cut + Mug', 'discount_type' => 'fixed', 'discount_value' => 15,
            'is_active' => 1, 'service_ids' => [$haircut->id], 'product_ids' => [$mug->id],
        ])->assertRedirect(route('services.index', ['tab' => 'combos']));

        $combo = ServiceCombo::first();
        $this->assertSame(1, $combo->products->count());
        $this->assertSame(1, $combo->services->count());
        $this->assertEquals(150.0, $combo->total_price);
        $this->assertEquals(135.0, $combo->combo_price);
    }

    public function test_a_single_item_is_rejected_regardless_of_type(): void
    {
        $tenant  = $this->tenant();
        $manager = $this->manager($tenant);
        $mug     = $this->product($tenant, ['name' => 'Mug', 'price' => 50]);

        $response = $this->actingAs($manager)->post(route('combos.store'), [
            'name' => 'Just Mug', 'discount_type' => 'percentage', 'discount_value' => 10,
            'is_active' => 1, 'product_ids' => [$mug->id],
        ]);

        $response->assertSessionHasErrors('service_ids');
        $this->assertSame(0, ServiceCombo::count());
    }

    public function test_a_pure_service_combo_behaves_identically_to_before_product_support(): void
    {
        // total_service_price must equal total_price for a combo with no
        // products — the whole point of keeping it as its own accessor.
        $tenant   = $this->tenant();
        $manager  = $this->manager($tenant);
        $haircut  = $this->service($tenant, ['name' => 'Haircut', 'price' => 100]);
        $shave    = $this->service($tenant, ['name' => 'Shave', 'price' => 50]);

        $this->actingAs($manager)->post(route('combos.store'), [
            'name' => 'Cut + Shave', 'discount_type' => 'percentage', 'discount_value' => 20,
            'is_active' => 1, 'service_ids' => [$haircut->id, $shave->id],
        ]);

        $combo = ServiceCombo::first();
        $this->assertEquals(150.0, $combo->total_service_price);
        $this->assertEquals(0.0, $combo->total_product_price);
        $this->assertEquals(150.0, $combo->total_price);
        $this->assertEquals(120.0, $combo->combo_price);
    }

    public function test_updating_a_combo_resyncs_products_without_disturbing_services(): void
    {
        $tenant  = $this->tenant();
        $manager = $this->manager($tenant);
        $mug     = $this->product($tenant, ['name' => 'Mug', 'price' => 50]);
        $coaster = $this->product($tenant, ['name' => 'Coaster', 'price' => 20]);
        $haircut = $this->service($tenant, ['name' => 'Haircut', 'price' => 100]);

        $combo = ServiceCombo::create([
            'tenant_id' => $tenant->id, 'name' => 'X', 'discount_type' => 'percentage',
            'discount_value' => 10, 'is_active' => true,
        ]);
        $combo->products()->sync([$mug->id, $coaster->id]);
        $combo->services()->sync([$haircut->id]);

        $this->actingAs($manager)->put(route('combos.update', $combo), [
            'name' => 'X', 'discount_type' => 'percentage', 'discount_value' => 10,
            'is_active' => 1, 'service_ids' => [$haircut->id], 'product_ids' => [$mug->id],
        ]);

        $combo->refresh();
        $this->assertSame(1, $combo->products->count(), 'Coaster must be removed.');
        $this->assertSame('Mug', $combo->products->first()->name);
        $this->assertSame(1, $combo->services->count(), 'Haircut must still be attached.');
    }

    public function test_destroying_a_combo_detaches_both_products_and_services(): void
    {
        $tenant  = $this->tenant();
        $manager = $this->manager($tenant);
        $mug     = $this->product($tenant, ['name' => 'Mug', 'price' => 50]);
        $haircut = $this->service($tenant, ['name' => 'Haircut', 'price' => 100]);

        $combo = ServiceCombo::create([
            'tenant_id' => $tenant->id, 'name' => 'X', 'discount_type' => 'percentage',
            'discount_value' => 10, 'is_active' => true,
        ]);
        $combo->products()->sync([$mug->id]);
        $combo->services()->sync([$haircut->id]);

        $this->actingAs($manager)->delete(route('combos.destroy', $combo));

        $this->assertSame(0, ServiceCombo::count());
        $this->assertSame(0, \DB::table('combo_items')->count());
        // The product/service themselves must survive — only the pivot rows go.
        $this->assertNotNull($mug->fresh());
        $this->assertNotNull($haircut->fresh());
    }

    public function test_a_variant_product_is_excluded_from_the_picker(): void
    {
        $tenant  = $this->tenant();
        $manager = $this->manager($tenant);
        $this->product($tenant, ['name' => 'Plain Mug', 'price' => 50]);
        $this->product($tenant, ['name' => 'Variant Tee', 'price' => 200, 'has_variants' => true]);

        $response = $this->actingAs($manager)->get(route('combos.create'));

        $products = $response->viewData('products');
        $this->assertSame(1, $products->count());
        $this->assertSame('Plain Mug', $products->first()->name);
    }
}
