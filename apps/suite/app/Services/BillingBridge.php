<?php

namespace App\Services;

use App\Models\SyncQueue;
use App\Models\Tenant;
use App\Models\TenantModule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BillingBridge
{
    private string $baseUrl;
    private ?string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('billing.url', ''), '/');
        $this->apiKey  = config('billing.internal_key');
    }

    /**
     * Create a subscription in billing. Returns the billing subscription_id on
     * success. On failure (billing down / timeout), enqueues the sync for retry
     * and returns null — the module still activates in suite immediately.
     */
    public function createModuleSubscription(Tenant $tenant, string $moduleKey): ?int
    {
        $module = config("modules.{$moduleKey}");

        if (!$module) {
            Log::warning("BillingBridge: unknown module key [{$moduleKey}]");
            return null;
        }

        if (!$this->apiKey) {
            return null;
        }

        $payload = [
            'tenant_name'  => $tenant->name,
            'tenant_email' => $tenant->email,
            'tenant_phone' => $tenant->phone,
            'module_key'   => $moduleKey,
            'module_name'  => $module['name'],
            'module_price' => $module['price'],
        ];

        try {
            $response = Http::timeout(10)
                ->withHeaders(['X-Internal-Key' => $this->apiKey])
                ->post("{$this->baseUrl}/api/internal/module-subscriptions", $payload);

            if ($response->successful()) {
                // Clear any stale pending sync for this combo
                $this->clearPendingSync($tenant->id, $moduleKey, 'create_subscription');
                return $response->json('subscription_id');
            }

            $error = "HTTP {$response->status()}: {$response->body()}";

        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        Log::warning("BillingBridge: create_subscription queued for retry — {$error}", [
            'tenant' => $tenant->id,
            'module' => $moduleKey,
        ]);

        $this->enqueue('create_subscription', $tenant->id, $moduleKey, $payload, $error);

        return null;
    }

    /**
     * Cancel a subscription. On failure, enqueues the cancellation for retry.
     */
    public function cancelModuleSubscription(int $billingSubscriptionId, int $tenantId, string $moduleKey): bool
    {
        if (!$this->apiKey) {
            return false;
        }

        $payload = ['subscription_id' => $billingSubscriptionId];

        try {
            $response = Http::timeout(10)
                ->withHeaders(['X-Internal-Key' => $this->apiKey])
                ->post("{$this->baseUrl}/api/internal/module-subscriptions/cancel", $payload);

            if ($response->successful()) {
                $this->clearPendingSync($tenantId, $moduleKey, 'cancel_subscription');
                return true;
            }

            $error = "HTTP {$response->status()}: {$response->body()}";

        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        Log::warning("BillingBridge: cancel_subscription queued for retry — {$error}", [
            'tenant' => $tenantId,
            'module' => $moduleKey,
        ]);

        $this->enqueue('cancel_subscription', $tenantId, $moduleKey, $payload, $error);

        return false;
    }

    /**
     * Attempt a single queued sync item. Called by ProcessSyncQueue command.
     * Returns the billing_subscription_id on success, null on failure.
     */
    public function replayQueueItem(SyncQueue $item): ?int
    {
        if (!$this->apiKey) {
            return null;
        }

        try {
            if ($item->type === 'create_subscription') {
                $response = Http::timeout(10)
                    ->withHeaders(['X-Internal-Key' => $this->apiKey])
                    ->post("{$this->baseUrl}/api/internal/module-subscriptions", $item->payload);

                if ($response->successful()) {
                    return $response->json('subscription_id');
                }

                throw new \RuntimeException("HTTP {$response->status()}: {$response->body()}");
            }

            if ($item->type === 'cancel_subscription') {
                $response = Http::timeout(10)
                    ->withHeaders(['X-Internal-Key' => $this->apiKey])
                    ->post("{$this->baseUrl}/api/internal/module-subscriptions/cancel", $item->payload);

                if ($response->successful()) {
                    return -1; // Sentinel: success with no ID to store
                }

                throw new \RuntimeException("HTTP {$response->status()}: {$response->body()}");
            }

        } catch (\Throwable $e) {
            throw $e;
        }

        return null;
    }

    // ── Private helpers ────────────────────────────────────────────

    private function enqueue(string $type, int $tenantId, string $moduleKey, array $payload, string $error): void
    {
        // Avoid duplicates: upsert pending item if one already exists
        SyncQueue::updateOrCreate(
            [
                'type'      => $type,
                'tenant_id' => $tenantId,
                'module_key'=> $moduleKey,
                'status'    => 'pending',
            ],
            [
                'payload'       => $payload,
                'last_error'    => $error,
                'next_retry_at' => now()->addMinutes(5),
            ]
        );
    }

    private function clearPendingSync(int $tenantId, string $moduleKey, string $type): void
    {
        SyncQueue::where('tenant_id', $tenantId)
            ->where('module_key', $moduleKey)
            ->where('type', $type)
            ->whereIn('status', ['pending', 'retrying'])
            ->delete();
    }
}
