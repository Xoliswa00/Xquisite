<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Product;
use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionsController extends Controller
{
    public function index()
    {
        $company = auth()->user()->currentCompany;

        if (!$company) {
            return view('subscriptions.index', ['subscriptions' => collect()]);
        }

        $subscriptions = Subscription::where('company_id', $company->id)
            ->with('client', 'product')
            ->latest()
            ->paginate(15);

        return view('subscriptions.index', compact('subscriptions'));
    }

    public function create()
    {
        $company = auth()->user()->currentCompany;
        abort_if(!$company, 403, 'No active company.');

        $clients = Client::where('company_id', $company->id)->select('id', 'name')->get();
        $products = Product::where('company_id', $company->id)
            ->where('billing_type', 'recurring')
            ->select('id', 'name', 'price')
            ->get();

        return view('subscriptions.create', compact('clients', 'products'));
    }

    public function store(Request $request)
    {
        $company = auth()->user()->currentCompany;
        abort_if(!$company, 403, 'No active company.');

        $validated = $request->validate([
            'client_id'         => ['required', 'exists:clients,id'],
            'product_id'        => ['required', 'exists:products,id'],
            'start_date'        => ['required', 'date', 'after:today'],
            'frequency'         => ['required', 'in:monthly,quarterly,yearly'],
            'auto_renew'        => ['nullable', 'boolean'],
        ]);

        $product = Product::find($validated['product_id']);

        if ($product->billing_type !== 'recurring') {
            return back()->withErrors(['product_id' => 'Only recurring products can be subscribed to.']);
        }

        $nextInvoiceDate = match ($validated['frequency']) {
            'monthly'   => now()->addMonth(),
            'quarterly' => now()->addQuarters(1),
            'yearly'    => now()->addYear(),
        };

        $subscription = Subscription::create([
            'company_id'        => $company->id,
            'client_id'         => $validated['client_id'],
            'product_id'        => $validated['product_id'],
            'status'            => 'active',
            'start_date'        => $validated['start_date'],
            'frequency'         => $validated['frequency'],
            'next_invoice_date' => $nextInvoiceDate,
            'auto_renew'        => $validated['auto_renew'] ?? true,
        ]);

        return redirect()->route('subscriptions.show', $subscription)
            ->with('success', 'Subscription created successfully.');
    }

    public function show(Subscription $subscription)
    {
        $this->authorize('view', $subscription);
        $subscription->load('client', 'product', 'company');

        return view('subscriptions.show', compact('subscription'));
    }

    public function edit(Subscription $subscription)
    {
        $this->authorize('update', $subscription);

        $company = auth()->user()->currentCompany;
        $clients = Client::where('company_id', $company->id)->select('id', 'name')->get();
        $products = Product::where('company_id', $company->id)
            ->where('billing_type', 'recurring')
            ->select('id', 'name')
            ->get();

        return view('subscriptions.edit', compact('subscription', 'clients', 'products'));
    }

    public function update(Request $request, Subscription $subscription)
    {
        $this->authorize('update', $subscription);

        $validated = $request->validate([
            'status'            => ['required', 'in:active,paused,cancelled'],
            'next_invoice_date' => ['required', 'date', 'after_or_equal:today'],
            'auto_renew'        => ['nullable', 'boolean'],
        ]);

        $subscription->update([
            'status'            => $validated['status'],
            'next_invoice_date' => $validated['next_invoice_date'],
            'auto_renew'        => $validated['auto_renew'] ?? true,
            'end_date'          => $validated['status'] === 'cancelled' ? now() : null,
        ]);

        return redirect()->route('subscriptions.show', $subscription)
            ->with('success', 'Subscription updated.');
    }

    public function destroy(Subscription $subscription)
    {
        $this->authorize('delete', $subscription);

        $subscription->update(['status' => 'cancelled', 'end_date' => now()]);

        return redirect()->route('subscriptions.index')
            ->with('success', 'Subscription cancelled.');
    }
}
