<?php

namespace Tests\Feature\Billing;

use App\Models\PlatformModule;
use App\Models\Tenant;
use App\Services\PlatformBillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformBillingServiceTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(array $overrides = []): Tenant
    {
        return Tenant::create(array_merge([
            'name'      => 'Test Tenant',
            'slug'      => 'test-tenant-' . uniqid(),
            'email'     => 'tenant@example.com',
            'is_active' => true,
        ], $overrides));
    }

    public function test_tenant_with_no_active_modules_is_not_due_for_billing(): void
    {
        $tenant = $this->tenant();

        $due = app(PlatformBillingService::class)->tenantsDueForBilling();

        $this->assertFalse($due->contains('id', $tenant->id));
    }

    private function platformModule(float $price): PlatformModule
    {
        return PlatformModule::create([
            'key'         => 'bookings',
            'name'        => 'Bookings',
            'description' => 'Bookings module',
            'price'       => $price,
            'status'      => 'active',
        ]);
    }

    public function test_tenant_with_an_active_module_is_due_for_billing(): void
    {
        $tenant = $this->tenant();
        $module = $this->platformModule(199);
        $tenant->activateModule($module->key);

        $due = app(PlatformBillingService::class)->tenantsDueForBilling();

        $this->assertTrue($due->contains('id', $tenant->id));
    }

    public function test_demo_tenant_is_never_due_for_billing_even_with_active_modules(): void
    {
        $tenant = $this->tenant(['is_demo' => true]);
        $module = $this->platformModule(199);
        $tenant->activateModule($module->key);

        $due = app(PlatformBillingService::class)->tenantsDueForBilling();

        $this->assertFalse($due->contains('id', $tenant->id));
    }

    public function test_generate_invoice_uses_active_module_pricing_not_flat_amount(): void
    {
        $tenant = $this->tenant();
        $module = $this->platformModule(449);
        $tenant->activateModule($module->key);

        $invoice = app(PlatformBillingService::class)->generateInvoice($tenant);

        $this->assertSame(449.0, (float) $invoice->amount);
    }

    public function test_generate_invoice_throws_when_tenant_already_invoiced_this_period(): void
    {
        $tenant = $this->tenant();
        $module = $this->platformModule(199);
        $tenant->activateModule($module->key);

        app(PlatformBillingService::class)->generateInvoice($tenant);

        $this->expectException(\RuntimeException::class);
        app(PlatformBillingService::class)->generateInvoice($tenant);
    }
}
