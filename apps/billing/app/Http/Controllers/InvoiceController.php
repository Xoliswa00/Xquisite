<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function index()
    {
        $company = auth()->user()->currentCompany;

        if (!$company) {
            return view('invoices.index', ['invoices' => collect()]);
        }

        $invoices = $company->invoices()->with('client')->latest()->get();

        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $company = auth()->user()->currentCompany;

        $clients = $company
            ? Client::where('company_id', $company->id)->select('id', 'name')->get()
            : collect();

        return view('invoices.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $company = auth()->user()->currentCompany;
        abort_if(!$company, 403, 'No active company.');

        $validated = $request->validate([
            'client_id'           => ['required', 'exists:clients,id'],
            'due_date'            => ['required', 'date'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity'    => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'  => ['required', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($validated, $company) {
            $invoice = $company->invoices()->create([
                'client_id'      => $validated['client_id'],
                'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . Str::upper(Str::random(4)),
                'status'         => 'draft',
                'total'          => 0,
                'vat_total'      => 0,
                'due_date'       => $validated['due_date'],
            ]);

            $subtotal = 0;
            $vatTotal = 0;

            foreach ($validated['items'] as $item) {
                $lineTotal = (float) $item['quantity'] * (float) $item['unit_price'];
                $vatAmount = $lineTotal * 0.15;

                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'vat_amount'  => $vatAmount,
                    'total'       => $lineTotal,
                ]);

                $subtotal += $lineTotal;
                $vatTotal += $vatAmount;
            }

            $invoice->update([
                'total'     => round($subtotal + $vatTotal, 2),
                'vat_total' => round($vatTotal, 2),
            ]);

            return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice created.');
        });
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $invoice->load('client', 'items', 'payments');
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $company = auth()->user()->currentCompany;
        $clients = Client::where('company_id', $company->id)->select('id', 'name')->get();
        $invoice->load('items');

        return view('invoices.edit', compact('invoice', 'clients'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $validated = $request->validate([
            'client_id'           => ['required', 'exists:clients,id'],
            'due_date'            => ['required', 'date'],
            'status'              => ['required', 'in:draft,sent,paid,overdue,cancelled'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity'    => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'  => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($invoice, $validated) {
            $invoice->items()->delete();

            $subtotal = 0;
            $vatTotal = 0;

            foreach ($validated['items'] as $item) {
                $lineTotal = (float) $item['quantity'] * (float) $item['unit_price'];
                $vatAmount = $lineTotal * 0.15;

                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'vat_amount'  => $vatAmount,
                    'total'       => $lineTotal,
                ]);

                $subtotal += $lineTotal;
                $vatTotal += $vatAmount;
            }

            $invoice->update([
                'client_id' => $validated['client_id'],
                'due_date'  => $validated['due_date'],
                'status'    => $validated['status'],
                'total'     => round($subtotal + $vatTotal, 2),
                'vat_total' => round($vatTotal, 2),
            ]);
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice updated.');
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);
        abort_if($invoice->status === 'paid', 422, 'Paid invoices cannot be deleted.');
        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'Invoice deleted.');
    }

    public function send(Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        $invoice->update(['status' => 'sent']);
        return back()->with('success', 'Invoice marked as sent.');
    }

    public function download(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $invoice->load('client', 'items', 'company');

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'))
            ->setPaper('a4', 'portrait');

        return $pdf->download($invoice->invoice_number . '.pdf');
    }

    public function createFromQuote(Quote $quote)
    {
        $this->authorize('convert', $quote);

        abort_if($quote->status === 'invoiced', 422, 'This quote has already been converted.');

        $invoice = DB::transaction(function () use ($quote) {
            $company = auth()->user()->currentCompany;

            $invoice = $company->invoices()->create([
                'client_id'      => $quote->client_id,
                'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . Str::upper(Str::random(4)),
                'status'         => 'draft',
                'total'          => $quote->total,
                'vat_total'      => $quote->items->sum('vat_amount'),
                'due_date'       => now()->addDays(30),
            ]);

            foreach ($quote->items as $item) {
                $invoice->items()->create([
                    'product_id'  => $item->product_id,
                    'description' => $item->description,
                    'quantity'    => $item->quantity,
                    'unit_price'  => $item->unit_price,
                    'vat_amount'  => $item->vat_amount,
                    'total'       => $item->total,
                ]);
            }

            $quote->update(['status' => 'invoiced']);

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice created from quote ' . $quote->quote_number . '.');
    }
}
