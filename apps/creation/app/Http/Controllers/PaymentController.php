<?php

namespace App\Http\Controllers;

use App\Models\payment;
use App\Http\Requests\StorepaymentRequest;
use App\Http\Requests\UpdatepaymentRequest;
use App\Models\Invoice;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\PaymentReceivedNotification;
use App\Notifications\ClientPaymentConfirmedNotification;




class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
      public function store(Request $request, Invoice $invoice)
    {
        $this->authorize('pay', $invoice);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,eft,card,debit_order',
            'payment_date' => 'required|date',
            'reference' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function() use ($invoice, $validated) {
            $payment = $invoice->payments()->create([
                'company_id' => auth()->user()->currentCompany->id,
                'user_id' => auth()->id(),
                'amount' => $validated['amount'],
                'method' => $validated['method'],
                'payment_date' => $validated['payment_date'],
                'reference' => $validated['reference'] ?? null,
            ]);

            // Update invoice status if fully paid
            $totalPaid = $invoice->payments()->sum('amount');
            if ($totalPaid >= $invoice->total) {
                $invoice->update(['status' => 'paid']);
            }

            $companyName = auth()->user()->managedCompanies->first()->name ?? config('app.name');

            // Notify owner dashboard
            auth()->user()->notify(new PaymentReceivedNotification(
                invoiceNumber: $invoice->invoice_number ?? "INV-{$invoice->id}",
                amount:        (float) $validated['amount'],
                clientName:    optional($invoice->client)->name ?? 'Client',
                invoiceId:     $invoice->id,
            ));

            // Notify client with payment confirmation
            if ($invoice->client && $invoice->client->user) {
                $invoice->client->user->notify(new ClientPaymentConfirmedNotification(
                    invoiceNumber: $invoice->invoice_number ?? "INV-{$invoice->id}",
                    companyName:   $companyName,
                    amount:        (float) $validated['amount'],
                    invoiceId:     $invoice->id,
                ));
            }

            return $payment;
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(payment $payment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(payment $payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatepaymentRequest $request, payment $payment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(payment $payment)
    {
        //
    }
}
