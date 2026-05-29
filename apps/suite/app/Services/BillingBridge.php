<?php

namespace App\Services;

use App\Models\Tenant;
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
     * Create an active subscription in the billing app for a module.
     * Returns the billing subscription_id on success, null on failure.
     */
    public function createModuleSubscription(Tenant $tenant, string $moduleKey): ?int
    {
        $module = config("modules.{$moduleKey}");

        if (!$module) {
            Log::warning("BillingBridge: unknown module key [{$moduleKey}]");
            return null;
        }

        if (!$this->apiKey) {
            Log::warning('BillingBridge: BILLING_INTERNAL_KEY not set — skipping billing sync.');
            return null;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['X-Internal-Key' => $this->apiKey])
                ->post("{$this->baseUrl}/api/internal/module-subscriptions", [
                    'tenant_name'  => $tenant->name,
                    'tenant_email' => $tenant->email,
                    'tenant_phone' => $tenant->phone,
                    'module_key'   => $moduleKey,
                    'module_name'  => $module['name'],
                    'module_price' => $module['price'],
                ]);

            if ($response->successful()) {
                return $response->json('subscription_id');
            }

            Log::error('BillingBridge: module subscription failed', [
                'status'  => $response->status(),
                'body'    => $response->body(),
                'tenant'  => $tenant->id,
                'module'  => $moduleKey,
            ]);

        } catch (\Throwable $e) {
            Log::error('BillingBridge: HTTP error', [
                'message' => $e->getMessage(),
                'tenant'  => $tenant->id,
                'module'  => $moduleKey,
            ]);
        }

        return null;
    }

    /**
     * Cancel a subscription in the billing app.
     */
    public function cancelModuleSubscription(int $billingSubscriptionId): bool
    {
        if (!$this->apiKey) {
            return false;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['X-Internal-Key' => $this->apiKey])
                ->post("{$this->baseUrl}/api/internal/module-subscriptions/cancel", [
                    'subscription_id' => $billingSubscriptionId,
                ]);

            return $response->successful();

        } catch (\Throwable $e) {
            Log::error('BillingBridge: cancel error', [
                'message'         => $e->getMessage(),
                'subscription_id' => $billingSubscriptionId,
            ]);
            return false;
        }
    }
}
