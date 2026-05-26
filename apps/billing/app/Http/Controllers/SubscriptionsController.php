<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Http\Requests\StoresubscriptionsRequest;
use App\Http\Requests\UpdatesubscriptionsRequest;
use App\Models\Invoice;
use Illuminate\Http\Request;


class SubscriptionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
       public function store(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $validated = $request->validate([
            'frequency' => 'required|in:monthly,quarterly,yearly',
            'next_invoice_date' => 'required|date',
        ]);

        $subscription = Subscription::create([
            'invoice_id' => $invoice->id,
            'frequency' => $validated['frequency'],
            'next_invoice_date' => $validated['next_invoice_date'],
            'active' => true,
        ]);

        return response()->json($subscription, 201);
    }

    public function pause(Subscription $subscription)
    {
        $subscription->update(['active' => false]);
        return response()->json($subscription);
    }

    public function resume(Subscription $subscription)
    {
        $subscription->update(['active' => true]);
        return response()->json($subscription);
    }
}
