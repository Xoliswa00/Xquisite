<?php

namespace App\Jobs;

use App\Models\BillingQueue;
use App\Models\Tenant;
use App\Services\PlatformBillingService;
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

    public function handle(PlatformBillingService $billing): void
    {
        $billing->generateInvoice($this->tenant);
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
