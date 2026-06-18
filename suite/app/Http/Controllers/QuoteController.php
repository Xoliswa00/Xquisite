<?php

namespace App\Http\Controllers;

use App\Models\PaymentPlan;
use App\Models\Quote;
use App\Modules\Booking\Models\Customer;
use App\Notifications\QuoteStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class QuoteController extends Controller
{
    public function index()
    {
        $quotes = Quote::where('tenant_id', auth()->user()->tenant_id)
            ->with('customer')
            ->latest()
            ->paginate(30);

        return view('quotes.index', compact('quotes'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();

        return view('quotes.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'              => 'required|string|max:150',
            'customer_id'        => 'nullable|exists:customers,id',
            'client_email'       => 'nullable|email|max:150',
            'line_items'         => 'required|array|min:1',
            'line_items.*.name'  => 'required|string|max:150',
            'line_items.*.qty'   => 'required|numeric|min:0',
            'line_items.*.unit'  => 'nullable|string|max:30',
            'line_items.*.unit_price' => 'required|numeric|min:0',
            'tax_rate'           => 'nullable|numeric|min:0|max:100',
            'deposit_percentage' => 'nullable|numeric|min:0|max:100',
            'valid_until'        => 'nullable|date|after_or_equal:today',
            'notes'              => 'nullable|string|max:2000',
        ]);

        // Calculate line item totals
        $lineItems = collect($data['line_items'])->map(fn ($item) => array_merge($item, [
            'total' => round($item['qty'] * $item['unit_price'], 2),
        ]))->all();

        $subtotal  = collect($lineItems)->sum('total');
        $taxRate   = $data['tax_rate'] ?? 0;
        $taxAmount = round($subtotal * ($taxRate / 100), 2);

        $quote = Quote::create([
            'tenant_id'          => auth()->user()->tenant_id,
            'customer_id'        => $data['customer_id'] ?? null,
            'created_by'         => auth()->id(),
            'title'              => $data['title'],
            'line_items'         => $lineItems,
            'subtotal'           => $subtotal,
            'tax_rate'           => $taxRate,
            'tax_amount'         => $taxAmount,
            'total'              => $subtotal + $taxAmount,
            'deposit_percentage' => $data['deposit_percentage'] ?? 50,
            'valid_until'        => $data['valid_until'] ?? null,
            'notes'              => $data['notes'] ?? null,
            'client_email'       => $data['client_email'] ?? null,
        ]);

        return redirect()->route('quotes.show', $quote)
            ->with('success', "Quote {$quote->reference} created.");
    }

    public function show(Quote $quote)
    {
        $this->authorise($quote);
        $quote->load(['customer', 'paymentPlan.installments', 'appointment']);

        return view('quotes.show', compact('quote'));
    }

    public function send(Quote $quote)
    {
        $this->authorise($quote);

        $email = $quote->client_email ?? $quote->customer?->email;
        abort_unless($email, 422, 'No client email address on this quote.');

        // Send quote email with accept link
        Mail::to($email)->queue(new \App\Mail\QuoteMail($quote));

        $quote->update(['status' => 'sent']);

        // Notify the tenant owner
        $owner = auth()->user()->tenant->users()->where('role', 'owner')->first();
        if ($owner && $owner->id !== auth()->id()) {
            $owner->notify(new QuoteStatusNotification($quote, 'sent'));
        }

        return back()->with('success', "Quote {$quote->reference} sent to {$email}.");
    }

    public function destroy(Quote $quote)
    {
        $this->authorise($quote);
        abort_if(in_array($quote->status, ['accepted', 'converted']), 422, 'Cannot delete an accepted quote.');

        $quote->delete();

        return redirect()->route('quotes.index')->with('success', 'Quote deleted.');
    }

    private function authorise(Quote $quote): void
    {
        abort_unless($quote->tenant_id === auth()->user()->tenant_id, 403);
    }
}
