<?php

namespace Tests\Feature\Billing;

use App\Models\BillingSetting;
use App\Models\PlatformModule;
use App\Models\Tenant;
use App\Services\PlatformBillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformBillingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // BillingSetting::get caches for an hour; RefreshDatabase does not touch
        // the cache, so clear it so vat_rate from one test can't leak into another.
        cache()->flush();
    }

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
        return $this->platformModuleKeyed('bookings', 'Bookings', $price);
    }

    private function platformModuleKeyed(string $key, string $name, float $price): PlatformModule
    {
        return PlatformModule::create([
            'key'         => $key,
            'name'        => $name,
            'description' => $name . ' module',
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

    public function test_generate_invoice_snapshots_line_items_that_reconcile_with_the_amount(): void
    {
        $tenant = $this->tenant();
        $tenant->activateModule($this->platformModuleKeyed('bookings', 'Bookings', 199)->key);
        $tenant->activateModule($this->platformModuleKeyed('pos', 'Point of Sale', 150)->key);

        $invoice = app(PlatformBillingService::class)->generateInvoice($tenant);

        $this->assertIsArray($invoice->line_items);
        $this->assertCount(2, $invoice->line_items);
        $this->assertSame(349.0, (float) $invoice->amount);
        $this->assertSame(349.0, (float) array_sum(array_column($invoice->line_items, 'amount')));
        $this->assertEqualsCanonicalizing(
            ['Bookings', 'Point of Sale'],
            array_column($invoice->line_items, 'name'),
        );
    }

    public function test_generate_invoice_has_no_vat_by_default(): void
    {
        $tenant = $this->tenant();
        $tenant->activateModule($this->platformModule(200)->key);

        $invoice = app(PlatformBillingService::class)->generateInvoice($tenant);

        $this->assertSame(0.0, (float) $invoice->vat_amount);
        $this->assertSame(0.0, (float) $invoice->vat_rate);
        $this->assertSame(200.0, (float) $invoice->subtotal);
        $this->assertSame(200.0, (float) $invoice->amount);
        $this->assertFalse($invoice->isTaxInvoice());
    }

    public function test_generate_invoice_backs_out_inclusive_vat_and_freezes_the_rate(): void
    {
        BillingSetting::set('vat_rate', '15');

        $tenant = $this->tenant();
        $tenant->activateModule($this->platformModule(230)->key);

        $invoice = app(PlatformBillingService::class)->generateInvoice($tenant);

        // R230 already includes 15% VAT: VAT = 230 * 15/115 = 30, ex-VAT = 200,
        // total charged is unchanged at 230. The rate is frozen onto the row so
        // a later BillingSetting change can't reprint this invoice with a rate
        // its own figures contradict.
        $this->assertSame(230.0, (float) $invoice->amount);
        $this->assertSame(30.0, (float) $invoice->vat_amount);
        $this->assertSame(200.0, (float) $invoice->subtotal);
        $this->assertSame(15.0, (float) $invoice->vat_rate);

        BillingSetting::set('vat_rate', '18');
        $this->assertSame('15', $invoice->fresh()->vatRateLabel());
    }

    public function test_a_vat_amount_alone_is_not_a_tax_invoice_without_a_vat_number(): void
    {
        BillingSetting::set('vat_rate', '15');
        $tenant = $this->tenant();
        $tenant->activateModule($this->platformModule(230)->key);
        $invoice = app(PlatformBillingService::class)->generateInvoice($tenant);

        $this->assertFalse($invoice->isTaxInvoice());          // no company_vat set

        BillingSetting::set('company_vat', '4820314765');
        $this->assertTrue($invoice->isTaxInvoice());
    }

    public function test_vat_rate_setting_is_clamped_to_a_sane_range(): void
    {
        BillingSetting::set('vat_rate', 'not-a-number');
        $tenant = $this->tenant();
        $tenant->activateModule($this->platformModule(200)->key);
        $invoice = app(PlatformBillingService::class)->generateInvoice($tenant);
        $this->assertSame(0.0, (float) $invoice->vat_amount);

        BillingSetting::set('vat_rate', '-5');
        $tenant2 = $this->tenant();
        $tenant2->activateModule($this->platformModuleKeyed('pos', 'Point of Sale', 200)->key);
        $invoice2 = app(PlatformBillingService::class)->generateInvoice($tenant2);
        $this->assertSame(0.0, (float) $invoice2->vat_amount);
    }
}
