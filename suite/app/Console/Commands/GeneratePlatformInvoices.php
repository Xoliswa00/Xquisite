<?php

namespace App\Console\Commands;

use App\Models\BillingQueue;
use App\Models\BillingSetting;
use App\Services\PlatformBillingService;
use Illuminate\Console\Command;

class GeneratePlatformInvoices extends Command
{
    protected $signature   = 'billing:generate {--dry-run : Show who would be billed without generating}';
    protected $description = 'Generate monthly platform invoices for all tenants due this month.';

    public function handle(PlatformBillingService $billing): int
    {
        if (BillingSetting::get('auto_billing_enabled') === '0') {
            $this->info('Auto-billing is disabled in settings. Skipping.');
            return self::SUCCESS;
        }

        $due = $billing->tenantsDueForBilling();

        if ($due->isEmpty()) {
            $this->info('No tenants due for billing this month.');
            return self::SUCCESS;
        }

        $this->info("Found {$due->count()} tenant(s) due for billing.");

        if ($this->option('dry-run')) {
            $this->table(
                ['ID', 'Name', 'Plan', 'Amount'],
                $due->map(fn ($t) => [
                    $t->id,
                    $t->name,
                    $t->plan ?? 'basic',
                    'R' . number_format(\App\Models\Tenant::planAmount($t->plan ?? 'basic'), 2),
                ])
            );
            return self::SUCCESS;
        }

        $generated = 0;
        $queued    = 0;

        foreach ($due as $tenant) {
            try {
                $billing->generateInvoice($tenant);
                $generated++;
                $this->line("  <fg=green>✓</> {$tenant->name} — invoice generated.");
            } catch (\Throwable $e) {
                BillingQueue::create([
                    'tenant_id'    => $tenant->id,
                    'operation'    => 'generate_invoice',
                    'status'       => 'pending',
                    'max_attempts' => 5,
                    'last_error'   => $e->getMessage(),
                ]);
                $queued++;
                $this->line("  <fg=yellow>✗</> {$tenant->name} — queued for retry: {$e->getMessage()}");
            }
        }

        $this->info("Done — {$generated} generated, {$queued} queued for retry.");
        return self::SUCCESS;
    }
}
