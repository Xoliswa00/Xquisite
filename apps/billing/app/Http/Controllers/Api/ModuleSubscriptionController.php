<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Company;
use App\Models\Product;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModuleSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_name'    => 'required|string|max:255',
            'tenant_email'   => 'required|email',
            'tenant_phone'   => 'nullable|string|max:30',
            'module_key'     => 'required|string|max:100',
            'module_name'    => 'required|string|max:255',
            'module_price'   => 'required|numeric|min:0',
        ]);

        $company = Company::where('is_platform_company', true)->firstOrFail();

        return DB::transaction(function () use ($validated, $company) {

            // Find or create a client record for the tenant
            $client = Client::firstOrCreate(
                ['company_id' => $company->id, 'email' => $validated['tenant_email']],
                [
                    'name'  => $validated['tenant_name'],
                    'phone' => $validated['tenant_phone'] ?? null,
                ]
            );

            // Find or create a recurring product for this module
            $product = Product::firstOrCreate(
                ['company_id' => $company->id, 'name' => $validated['module_name']],
                [
                    'billing_type'  => 'recurring',
                    'billing_cycle' => 'monthly',
                ]
            );

            // Check if an active subscription already exists for this client + product
            $existing = Subscription::where('company_id', $company->id)
                ->where('client_id', $client->id)
                ->where('product_id', $product->id)
                ->whereIn('status', ['active', 'paused'])
                ->first();

            if ($existing) {
                return response()->json([
                    'subscription_id' => $existing->id,
                    'client_id'       => $client->id,
                    'status'          => 'existing',
                    'message'         => 'Subscription already active.',
                ]);
            }

            $subscription = Subscription::create([
                'company_id'        => $company->id,
                'client_id'         => $client->id,
                'product_id'        => $product->id,
                'status'            => 'active',
                'start_date'        => now()->toDateString(),
                'frequency'         => 'monthly',
                'next_invoice_date' => now()->addMonth()->toDateString(),
                'auto_renew'        => true,
            ]);

            return response()->json([
                'subscription_id' => $subscription->id,
                'client_id'       => $client->id,
                'status'          => 'created',
            ], 201);
        });
    }

    public function cancel(Request $request)
    {
        $validated = $request->validate([
            'subscription_id' => 'required|integer|exists:subscriptions,id',
        ]);

        $company = Company::where('is_platform_company', true)->firstOrFail();

        $subscription = Subscription::where('id', $validated['subscription_id'])
            ->where('company_id', $company->id)
            ->firstOrFail();

        $subscription->update([
            'status'   => 'cancelled',
            'end_date' => now()->toDateString(),
        ]);

        return response()->json(['message' => 'Subscription cancelled.']);
    }
}
