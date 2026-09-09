<?php

namespace Tests\Feature\Admin;

use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 2 coarse role gates: an `employee` can do day-to-day work (bookings,
 * customers, sales, reports) but is fenced out of catalogue/pricing, stock,
 * staff management, store settings and Property Management. `manager` keeps
 * full access. Enforced by `can:` middleware on the route groups in web.php.
 */
class StaffRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionRoleSeeder::class);
    }

    private function tenantWithModules(array $modules): Tenant
    {
        $tenant = Tenant::create(['name' => 'Test Biz', 'slug' => 'test-biz', 'is_active' => true]);

        foreach ($modules as $key) {
            $tenant->activateModule($key);
        }

        return $tenant;
    }

    private function user(Tenant $tenant, string $role): User
    {
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $user->assignRole($role);

        return $user;
    }

    public function test_employee_is_blocked_from_manager_only_areas(): void
    {
        $tenant = $this->tenantWithModules(['booking', 'pos', 'ecommerce', 'property_management']);
        $employee = $this->user($tenant, 'employee');

        foreach ([
            '/services',                 // booking catalogue + pricing
            '/staff',                    // staff records
            '/products',                 // POS catalogue
            '/stock/take',               // stock
            '/purchase-orders',          // procurement
            '/suppliers',
            '/combos',
            '/promotions',
            '/store/settings',           // ecommerce config
            '/properties',               // property management (whole module)
            '/leases',
            '/rent-payments',
        ] as $path) {
            $this->actingAs($employee)->get($path)
                ->assertForbidden();
        }
    }

    public function test_employee_can_still_do_day_to_day_work(): void
    {
        $tenant = $this->tenantWithModules(['booking', 'pos']);
        $employee = $this->user($tenant, 'employee');

        $this->actingAs($employee)->get('/appointments')->assertOk();
        $this->actingAs($employee)->get('/customers')->assertOk();
        $this->actingAs($employee)->get('/staff/dashboard')->assertOk();
        $this->actingAs($employee)->get('/pos')->assertOk();
        $this->actingAs($employee)->get('/pos/sales')->assertOk();
    }

    public function test_manager_keeps_full_access(): void
    {
        $tenant = $this->tenantWithModules(['booking', 'pos', 'ecommerce', 'property_management']);
        $manager = $this->user($tenant, 'manager');

        foreach ([
            '/services', '/staff', '/products', '/stock/take', '/purchase-orders',
            '/suppliers', '/combos', '/promotions', '/store/settings',
            '/properties', '/leases', '/rent-payments',
        ] as $path) {
            $this->actingAs($manager)->get($path)
                ->assertOk();
        }
    }

    public function test_employee_lands_off_the_owner_dashboard(): void
    {
        $tenant = $this->tenantWithModules(['booking']);
        $employee = $this->user($tenant, 'employee');

        $this->actingAs($employee)->get('/dashboard')
            ->assertRedirect(route('appointments.calendar'));
    }
}
