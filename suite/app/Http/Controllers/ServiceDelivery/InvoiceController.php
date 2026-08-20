<?php

namespace App\Http\Controllers\ServiceDelivery;

use App\Http\Controllers\Controller;
use App\Models\BillingSetting;
use App\Models\Client;
use App\Modules\ServiceDelivery\Models\Gig;
use App\Modules\ServiceDelivery\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('client')->latest('issue_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->paginate(20)->withQueryString();

        return view('service-delivery.invoices.index', compact('invoices'));
    }

    public function create(Request $request)
    {
        $clients = Client::orderBy('name')->get();
        $gig = $request->filled('gig_id') ? Gig::with('client')->find($request->gig_id) : null;
        $vatRate = Invoice::defaultVatRate();
        $dueDays = (int) BillingSetting::get('invoice_due_days');

        return view('service-delivery.invoices.create', compact('clients', 'gig', 'vatRate', 'dueDays'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'       => 'required|exists:clients,id',
            'gig_id'          => 'nullable|exists:gigs,id',
            'issue_date'      => 'required|date',
            'due_date'        => 'required|date|after_or_equal:issue_date',
            'payment_terms'   => 'required|string|max:60',
            'tax_rate'        => 'required|numeric|min:0|max:100',
            'notes'           => 'nullable|string|max:2000',
            'line_items'                     => 'required|array|min:1',
            'line_items.*.description'       => 'required|string|max:255',
            'line_items.*.quantity'          => 'required|numeric|min:0',
            'line_items.*.unit_price'        => 'required|numeric|min:0',
            'line_items.*.discount_percent'  => 'nullable|numeric|min:0|max:100',
        ]);

        $tenantId = auth()->user()->tenant_id;

        $invoice = new Invoice([
            'tenant_id'      => $tenantId,
            'client_id'      => $validated['client_id'],
            'gig_id'         => $validated['gig_id'] ?? null,
            'invoice_number' => Invoice::generateNumber($tenantId),
            'status'         => 'draft',
            'issue_date'     => $validated['issue_date'],
            'due_date'       => $validated['due_date'],
            'payment_terms'  => $validated['payment_terms'],
            'line_items'     => $validated['line_items'],
            'tax_rate'       => $validated['tax_rate'],
            'notes'          => $validated['notes'] ?? null,
        ]);

        $invoice->recalculate();
        $invoice->save();

        return redirect()->route('invoices.show', $invoice)->with('success', "Invoice {$invoice->invoice_number} created.");
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['client', 'gig']);

        return view('service-delivery.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        abort_if($invoice->status === 'paid', 422, 'A paid invoice cannot be edited.');

        $clients = Client::orderBy('name')->get();

        return view('service-delivery.invoices.edit', compact('invoice', 'clients'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        abort_if($invoice->status === 'paid', 422, 'A paid invoice cannot be edited.');

        $validated = $request->validate([
            'issue_date'      => 'required|date',
            'due_date'        => 'required|date|after_or_equal:issue_date',
            'payment_terms'   => 'required|string|max:60',
            'tax_rate'        => 'required|numeric|min:0|max:100',
            'notes'           => 'nullable|string|max:2000',
            'line_items'                     => 'required|array|min:1',
            'line_items.*.description'       => 'required|string|max:255',
            'line_items.*.quantity'          => 'required|numeric|min:0',
            'line_items.*.unit_price'        => 'required|numeric|min:0',
            'line_items.*.discount_percent'  => 'nullable|numeric|min:0|max:100',
        ]);

        $invoice->fill($validated);
        $invoice->recalculate();
        $invoice->save();

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice updated.');
    }

    public function send(Invoice $invoice)
    {
        abort_if($invoice->status === 'paid', 422, 'Already paid.');

        $invoice->update(['status' => 'sent']);

        return back()->with('success', "Invoice {$invoice->invoice_number} marked as sent.");
    }

    public function recordPayment(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount_paid'    => 'required|numeric|min:0.01',
            'paid_at'        => 'required|date',
            'payment_method' => 'required|in:eft,cash,card,debit_order,other',
            'payment_reference' => 'nullable|string|max:100',
        ]);

        $totalPaid = (float) $invoice->amount_paid + (float) $validated['amount_paid'];
        $status = $totalPaid >= (float) $invoice->total ? 'paid' : $invoice->status;

        $invoice->update([
            'amount_paid'        => $totalPaid,
            'paid_at'            => $validated['paid_at'],
            'payment_method'     => $validated['payment_method'],
            'payment_reference'  => $validated['payment_reference'] ?? $invoice->payment_reference,
            'status'             => $status,
        ]);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', $status === 'paid' ? 'Payment recorded — fully paid.' : 'Partial payment recorded.');
    }

    public function cancel(Invoice $invoice)
    {
        abort_if($invoice->status === 'paid', 422, 'A paid invoice cannot be cancelled.');

        $invoice->update(['status' => 'cancelled']);

        return back()->with('success', "Invoice {$invoice->invoice_number} cancelled.");
    }

    public function downloadPdf(Invoice $invoice)
    {
        $invoice->load('client');

        $pdf = Pdf::loadView('service-delivery.invoices.invoice-pdf', compact('invoice'));
        $pdf->getDomPDF()->addInfo('Title', "Invoice {$invoice->invoice_number}");
        $pdf->getDomPDF()->addInfo('Author', BillingSetting::get('company_name') ?: config('app.name'));

        return $pdf->download("{$invoice->invoice_number}.pdf");
    }
}
