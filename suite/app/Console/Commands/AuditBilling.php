<?php

namespace App\Console\Commands;

use App\Models\PlatformInvoice;
use App\Models\Tenant;
use Illuminate\Console\Command;

class AuditBilling extends Command
{
    protected $signature   = 'billing:audit';
    protected $description = 'Read-only report of tenants likely affected by the no-subscription-check billing bug: suspended or invoiced while having zero active modules.';

    public function handle(): int
    {
        $suspendedNoModules = Tenant::whereNotNull('suspended_at')
            ->whereDoesntHave('activeModules')
            ->get();

        $invoicedNoModules = Tenant::whereHas('platformInvoices')
            ->whereDoesntHave('activeModules')
            ->with(['platformInvoices' => fn ($q) => $q->orderByDesc('billing_period_start')])
            ->get();

        $this->info('=== Suspended tenants with zero active modules (wrongly suspended candidates) ===');
        if ($suspendedNoModules->isEmpty()) {
            $this->line('  None found.');
        } else {
            $this->table(
                ['ID', 'Name', 'Email', 'Suspended At', 'Unpaid Invoices'],
                $suspendedNoModules->map(fn (Tenant $t) => [
                    $t->id,
                    $t->name,
                    $t->email,
                    $t->suspended_at?->toDateTimeString(),
                    $t->unpaidPlatformInvoices()->count(),
                ])
            );
        }

        $this->newLine();
        $this->info('=== Tenants invoiced while having zero active modules (wrongly billed) ===');
        if ($invoicedNoModules->isEmpty()) {
            $this->line('  None found.');
        } else {
            $rows = [];
            foreach ($invoicedNoModules as $tenant) {
                foreach ($tenant->platformInvoices as $invoice) {
                    $rows[] = [
                        $tenant->id,
                        $tenant->name,
                        $invoice->invoice_number,
                        $invoice->status,
                        'R' . number_format($invoice->amount, 2),
                        $invoice->billing_period_start,
                    ];
                }
            }
            $this->table(['Tenant ID', 'Name', 'Invoice #', 'Status', 'Amount', 'Period Start'], $rows);
        }

        $this->newLine();
        $this->info('This command makes no changes. Review each tenant before voiding invoices or lifting suspensions.');

        return self::SUCCESS;
    }
}
