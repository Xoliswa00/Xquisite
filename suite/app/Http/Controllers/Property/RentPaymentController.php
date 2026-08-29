<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Modules\Property\Models\RentPayment;
use App\Notifications\RentPaymentReceivedNotification;
use App\Services\Property\RentCycleService;
use Illuminate\Http\Request;

class RentPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = RentPayment::with(['lease.property', 'unit', 'renter'])->latest('due_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        $payments = $query->paginate(30)->withQueryString();

        return view('property.payments.index', compact('payments'));
    }

    public function show(RentPayment $rentPayment)
    {
        $rentPayment->load(['lease.property', 'unit', 'renter']);
        return view('property.payments.show', compact('rentPayment'));
    }

    public function record(Request $request, RentPayment $rentPayment)
    {
        $validated = $request->validate([
            'amount_paid'    => 'required|numeric|min:0.01',
            'paid_date'      => 'required|date',
            'payment_method' => 'required|in:eft,cash,card,debit_order,other',
            'reference'      => 'nullable|string|max:100',
            'notes'          => 'nullable|string',
        ]);

        $amountPaid = (float) $validated['amount_paid'];
        $status = $amountPaid >= $rentPayment->amount_due ? 'paid' : 'partial';

        $rentPayment->update(array_merge($validated, [
            'amount_paid' => $amountPaid,
            'status'      => $status,
        ]));

        if ($rentPayment->renter?->email) {
            $rentPayment->renter->notify(new RentPaymentReceivedNotification($rentPayment));
        }

        return redirect()->route('rent-payments.show', $rentPayment)
            ->with('success', $status === 'paid' ? 'Payment recorded — fully paid.' : 'Partial payment recorded.');
    }

    public function receiptPdf(RentPayment $rentPayment)
    {
        abort_if($rentPayment->amount_paid <= 0, 422, 'No payment has been recorded for this period yet.');

        $rentPayment->load(['unit.property', 'renter']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('property.payments.receipt-pdf', compact('rentPayment'));

        return $pdf->download('receipt-rent-' . $rentPayment->id . '.pdf');
    }

    /** Generate monthly payment records for all active leases */
    public function generateMonthly(RentCycleService $rentCycle)
    {
        $created = $rentCycle->generateMonthly();

        return back()->with('success', "{$created} new payment record(s) generated for " . now()->format('F Y') . '.');
    }

    /** Mark overdue — flag any pending payments past due date */
    public function flagOverdue(RentCycleService $rentCycle)
    {
        $count = $rentCycle->flagOverdue();

        return back()->with('success', "{$count} payment(s) marked as overdue.");
    }
}
