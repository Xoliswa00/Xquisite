<?php

namespace App\Console\Commands;

use App\Models\SyncQueue;
use App\Models\TenantModule;
use App\Services\BillingBridge;
use Illuminate\Console\Command;

class ProcessSyncQueue extends Command
{
    protected $signature   = 'billing:sync-queue {--dry-run : Show what would be processed without making any calls}';
    protected $description = 'Retry pending billing sync items (module subscriptions/cancellations).';

    public function handle(BillingBridge $billing): int
    {
        $due = SyncQueue::due()->with('tenant')->get();

        if ($due->isEmpty()) {
            $this->info('No pending sync items due for retry.');
            return self::SUCCESS;
        }

        $this->info("Processing {$due->count()} sync item(s)…");

        if ($this->option('dry-run')) {
            $this->table(
                ['ID', 'Type', 'Tenant', 'Module', 'Attempts', 'Last Error'],
                $due->map(fn ($i) => [
                    $i->id,
                    $i->type,
                    $i->tenant?->name ?? $i->tenant_id,
                    $i->module_key,
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
                $result = $billing->replayQueueItem($item);

                if ($result !== null) {
                    $billingId = $result === -1 ? null : $result;

                    $item->markCompleted($billingId ?? 0);

                    // Backfill billing_subscription_id on tenant_modules for create events
                    if ($item->type === 'create_subscription' && $billingId) {
                        TenantModule::where('tenant_id', $item->tenant_id)
                            ->where('module', $item->module_key)
                            ->whereNull('billing_subscription_id')
                            ->update(['billing_subscription_id' => $billingId]);
                    }

                    $this->line("  <fg=green>✓</> [{$item->type}] {$item->tenant?->name} / {$item->module_key}" .
                                ($billingId ? " — billing ID {$billingId}" : ''));
                    $succeeded++;
                } else {
                    throw new \RuntimeException('No subscription ID returned from billing.');
                }

            } catch (\Throwable $e) {
                $item->markFailed($e->getMessage());

                $status = $item->fresh()->status;
                $suffix = $status === 'abandoned'
                    ? ' <fg=red>(abandoned after max attempts)</>'
                    : " (retry in {$item->nextRetryDelay()} min)";

                $this->line("  <fg=yellow>✗</> [{$item->type}] {$item->tenant?->name} / {$item->module_key} — {$e->getMessage()}{$suffix}");
                $failed++;
            }
        }

        $this->info("Done — {$succeeded} succeeded, {$failed} failed.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
