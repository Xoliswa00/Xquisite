<?php

namespace App\Console\Commands;

use App\Models\BillingQueue;
use App\Services\PlatformBillingService;
use Illuminate\Console\Command;

class ProcessBillingQueue extends Command
{
    protected $signature   = 'billing:process-queue {--dry-run : Show pending items without processing}';
    protected $description = 'Process pending billing queue items (invoice generation retries).';

    public function handle(PlatformBillingService $billing): int
    {
        $due = BillingQueue::due()->with('tenant')->get();

        if ($due->isEmpty()) {
            $this->info('No pending billing queue items.');
            return self::SUCCESS;
        }

        $this->info("Processing {$due->count()} billing queue item(s)…");

        if ($this->option('dry-run')) {
            $this->table(
                ['ID', 'Operation', 'Tenant', 'Attempts', 'Last Error'],
                $due->map(fn ($i) => [
                    $i->id,
                    $i->operation,
                    $i->tenant?->name ?? "tenant #{$i->tenant_id}",
                    $i->attempts,
                    str($i->last_error)->limit(60),
                ])
            );
            return self::SUCCESS;
        }

        $succeeded = 0;
        $failed    = 0;

        foreach ($due as $item) {
            $item->markRetrying();

            try {
                match ($item->operation) {
                    'generate_invoice' => $this->generateInvoice($billing, $item),
                    default            => throw new \RuntimeException("Unknown operation: {$item->operation}"),
                };
                $succeeded++;
            } catch (\Throwable $e) {
                $item->markFailed($e->getMessage());

                $fresh  = $item->fresh();
                $suffix = $fresh->status === 'abandoned'
                    ? ' <fg=red>(abandoned after max attempts)</>'
                    : " (retry in {$item->nextRetryDelay()} min)";

                $this->line("  <fg=yellow>✗</> [{$item->operation}] {$item->tenant?->name} — {$e->getMessage()}{$suffix}");
                $failed++;
            }
        }

        $this->info("Done — {$succeeded} succeeded, {$failed} failed.");
        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function generateInvoice(PlatformBillingService $billing, BillingQueue $item): void
    {
        if (! $item->tenant) {
            throw new \RuntimeException("Tenant #{$item->tenant_id} not found.");
        }

        $invoice = $billing->generateInvoice($item->tenant);
        $item->markCompleted($invoice->id);

        $this->line("  <fg=green>✓</> [generate_invoice] {$item->tenant->name} — {$invoice->invoice_number}");
    }
}
