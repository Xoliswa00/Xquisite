<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Company;
use App\Models\Product;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaseSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'renter_name'    => 'required|string|max:255',
            'renter_email'   => 'required|email',
            'renter_phone'   => 'nullable|string|max:30',
            'property_name'  => 'required|string|max:255',
            'unit_number'    => 'required|string|max:50',
            'monthly_rent'   => 'required|numeric|min:0',
            'start_date'     => 'required|date',
        ]);

        $company = Company::where('is_platform_company', true)->firstOrFail();

        return DB::transaction(function () use ($validated, $company) {
            $client = Client::firstOrCreate(
                ['company_id' => $company->id, 'email' => $validated['renter_email']],
                ['name' => $validated['renter_name'], 'phone' => $validated['renter_phone'] ?? null]
            );

            $productName = "Rent — {$validated['unit_number']}, {$validated['property_name']}";
            $product = Product::firstOrCreate(
                ['company_id' => $company->id, 'name' => $productName],
                ['billing_type' => 'recurring', 'billing_cycle' => 'monthly']
            );

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
                ]);
            }

            $subscription = Subscription::create([
                'company_id'        => $company->id,
                'client_id'         => $client->id,
                'product_id'        => $product->id,
                'status'            => 'active',
                'start_date'        => $validated['start_date'],
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

        $subscription->update(['status' => 'cancelled', 'end_date' => now()->toDateString()]);

        return response()->json(['message' => 'Lease subscription cancelled.']);
    }
}
