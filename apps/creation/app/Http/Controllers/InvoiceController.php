<?php

namespace App\Http\Controllers;

use App\Models\invoice;
use App\Http\Requests\StoreinvoiceRequest;
use App\Http\Requests\UpdateinvoiceRequest;
use App\Models\Company;
use App\Models\Invoice_items;
use App\Models\Payment;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class InvoiceController extends Controller
{
  
  public function index()
    {
        $invoices = auth()->user()->currentCompany->invoices()->latest()->get();
        return response()->json($invoices);
    }

    public function createFromQuote(Quote $quote)
    {
        $this->authorize('convert', $quote);

        return DB::transaction(function() use ($quote) {
            $invoice = auth()->user()->currentCompany->invoices()->create([
                'client_id' => $quote->client_id,
                'invoice_number' => 'INV-' . Str::upper(Str::random(6)),
                'status' => 'draft',
                'total' => $quote->total,
                'vat_total' => $quote->items->sum('vat_amount'),
                'due_date' => now()->addDays(30),
            ]);

            foreach ($quote->items as $item) {
                $invoice->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'vat_amount' => $item->vat_amount,
                    'total' => $item->total,
                ]);
            }

            $quote->update(['status' => 'approved']);
            return $invoice;
        });
    }
}
