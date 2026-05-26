<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index()
    {
        $company = auth()->user()->currentCompany;

        if (!$company) {
            return view('payments.index', [
                'payments' => collect(),
                'stats'    => ['total' => 0, 'this_month' => 0, 'count' => 0],
            ]);
        }

        $payments = Payment::where('company_id', $company->id)
            ->with(['invoice.client'])
            ->orderByDesc('payment_date')
            ->paginate(20);

        $stats = [
            'total'      => Payment::where('company_id', $company->id)->sum('amount'),
            'this_month' => Payment::where('company_id', $company->id)
                ->whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount'),
            'count'      => Payment::where('company_id', $company->id)->count(),
        ];

        return view('payments.index', compact('payments', 'stats'));
    }

    public function show(Payment $payment)
    {
        $this->authorize('view', $payment);
        $payment->load(['invoice.client']);
        return view('payments.show', compact('payment'));
    }

    public function store(Request $request, Invoice $invoice)
    {
        $this->authorize('pay', $invoice);

        $validated = $request->validate([
            'amount'       => 'required|numeric|min:0.01',
            'method'       => 'required|in:cash,eft,card,debit_order',
            'payment_date' => 'required|date',
            'reference'    => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($invoice, $validated) {
            $invoice->payments()->create([
                'company_id'   => $invoice->company_id,
                'user_id'      => auth()->id(),
                'amount'       => $validated['amount'],
                'method'       => $validated['method'],
                'payment_date' => $validated['payment_date'],
                'reference'    => $validated['reference'] ?? null,
            ]);

            $totalPaid = $invoice->payments()->sum('amount');

            if ($totalPaid >= $invoice->total) {
                $invoice->update(['status' => 'paid']);
            }
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Payment recorded.');
    }

    public function destroy(Payment $payment)
    {
        $this->authorize('delete', $payment);

        $invoice = $payment->invoice;
        $payment->delete();

        if ($invoice && $invoice->status === 'paid') {
            $totalPaid = $invoice->payments()->sum('amount');
            if ($totalPaid < $invoice->total) {
                $invoice->update(['status' => 'sent']);
            }
        }

        return redirect()->back()->with('success', 'Payment deleted.');
    }
}
