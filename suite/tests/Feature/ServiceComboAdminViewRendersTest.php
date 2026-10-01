<?php

namespace Tests\Feature;

use App\Models\ServiceCategory;
use App\Models\ServiceCombo;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Booking\Models\Service;
use App\Modules\POS\Models\Product;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Renders the actual admin views end-to-end (not just compiles them) —
 * catches anything a Blade compile-check alone wouldn't, like an
 * undefined variable/relation access that only fails when real data flows
 * through the template.
 */
class ServiceComboAdminViewRendersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionRoleSeeder::class);
    }

    public function test_the_create_page_renders_with_products_and_services_available(): void
    {
        $tenant = Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
        $tenant->activateModule('pos');
        $manager = User::factory()->create(['tenant_id' => $tenant->id]);
        $manager->assignRole('manager');

        $category = ServiceCategory::create(['tenant_id' => $tenant->id, 'name' => 'Cat', 'icon' => 'x']);
        Service::create(['tenant_id' => $tenant->id, 'name' => 'Haircut', 'price' => 100, 'duration_minutes' => 30, 'is_active' => true, 'service_category_id' => $category->id]);
        Product::create(['tenant_id' => $tenant->id, 'name' => 'Mug', 'price' => 50, 'is_active' => true, 'is_available_online' => true]);

        $response = $this->actingAs($manager)->get(route('combos.create'));

        $response->assertOk();
        $response->assertSee('Select Services');
        $response->assertSee('Select Products');
        $response->assertSee('Haircut');
        $response->assertSee('Mug');
    }

    public function test_the_edit_page_renders_a_pre_populated_mixed_combo(): void
    {
        $tenant = Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
        $tenant->activateModule('pos');
        $manager = User::factory()->create(['tenant_id' => $tenant->id]);
        $manager->assignRole('manager');

        $category = ServiceCategory::create(['tenant_id' => $tenant->id, 'name' => 'Cat', 'icon' => 'x']);
        $service = Service::create(['tenant_id' => $tenant->id, 'name' => 'Haircut', 'price' => 100, 'duration_minutes' => 30, 'is_active' => true, 'service_category_id' => $category->id]);
        $product = Product::create(['tenant_id' => $tenant->id, 'name' => 'Mug', 'price' => 50, 'is_active' => true, 'is_available_online' => true]);

        $combo = ServiceCombo::create(['tenant_id' => $tenant->id, 'name' => 'Cut + Mug', 'discount_type' => 'percentage', 'discount_value' => 10, 'is_active' => true]);
        $combo->services()->sync([$service->id]);
        $combo->products()->sync([$product->id]);

        $response = $this->actingAs($manager)->get(route('combos.edit', $combo));

        $response->assertOk();
        $response->assertSee('Cut + Mug');
    }

    public function test_the_combos_list_tab_renders_a_mixed_combo(): void
    {
        $tenant = Tenant::create(['name' => 'Test Store', 'slug' => 'test-store', 'is_active' => true]);
        $tenant->activateModule('pos');
        $tenant->activateModule('booking'); // services.index itself is gated behind module:booking
        $manager = User::factory()->create(['tenant_id' => $tenant->id]);
        $manager->assignRole('manager');

        $category = ServiceCategory::create(['tenant_id' => $tenant->id, 'name' => 'Cat', 'icon' => 'x']);
        $service = Service::create(['tenant_id' => $tenant->id, 'name' => 'Haircut', 'price' => 100, 'duration_minutes' => 30, 'is_active' => true, 'service_category_id' => $category->id]);
        $product = Product::create(['tenant_id' => $tenant->id, 'name' => 'Mug', 'price' => 50, 'is_active' => true, 'is_available_online' => true]);

        $combo = ServiceCombo::create(['tenant_id' => $tenant->id, 'name' => 'Cut + Mug', 'discount_type' => 'percentage', 'discount_value' => 10, 'is_active' => true]);
        $combo->services()->sync([$service->id]);
        $combo->products()->sync([$product->id]);

        $response = $this->actingAs($manager)->get(route('services.index', ['tab' => 'combos']));

        $response->assertOk();
        $response->assertSee('Cut + Mug');
        $response->assertSee('Haircut, Mug');
        // total_price (150) line-through, combo_price (135) bold.
        $response->assertSee('150.00');
        $response->assertSee('135.00');
    }
}
