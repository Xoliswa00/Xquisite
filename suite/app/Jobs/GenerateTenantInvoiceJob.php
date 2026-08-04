<?php

namespace App\Jobs;

use App\Models\BillingQueue;
use App\Models\Tenant;
use App\Services\PlatformBillingService;
use App\Services\Tenant\TenantContext;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateTenantInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(public readonly Tenant $tenant) {}

    /**
     * Neither PlatformInvoice nor Tenant is HasTenant-scoped today, so this
     * isn't fixing a live bug — it establishes the convention future jobs
     * touching tenant-scoped models must follow.
     */
    public function handle(PlatformBillingService $billing): void
    {
        TenantContext::set($this->tenant->id);

        try {
            $billing->generateInvoice($this->tenant);
        } finally {
            TenantContext::clear();
        }
    }

    public function failed(\Throwable $exception): void
    {
        BillingQueue::create([
            'tenant_id'    => $this->tenant->id,
            'operation'    => 'generate_invoice',
            'status'       => 'pending',
            'max_attempts' => 5,
            'last_error'   => $exception->getMessage(),
        ]);
    }
}
