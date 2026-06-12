<?php

namespace App\Http\Controllers;

use App\Models\invoice_items;
use App\Http\Requests\Storeinvoice_itemsRequest;
use App\Http\Requests\Updateinvoice_itemsRequest;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceItemsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function store(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function() use ($invoice, $validated) {

            $product = auth()->user()
                ->currentCompany
                ->products()
                ->findOrFail($validated['product_id']);

            $vat = $product->price * ($product->vat_rate / 100);
            $lineTotal = ($product->price + $vat) * $validated['quantity'];

            $item = $invoice->items()->create([
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
                'unit_price' => $product->price,
                'vat_amount' => $vat,
                'total' => $lineTotal,
            ]);

            $this->recalculateInvoice($invoice);

            return $item;
        });
    }

    public function destroy(invoice_items $invoiceItem)
    {
        $invoice = $invoiceItem->invoice;
        $this->authorize('update', $invoice);

        DB::transaction(function() use ($invoiceItem, $invoice) {
            $invoiceItem->delete();
            $this->recalculateInvoice($invoice);
        });

        return response()->noContent();
    }

    private function recalculateInvoice(Invoice $invoice)
    {
        $total = $invoice->items()->sum('total');
        $vatTotal = $invoice->items()->sum('vat_amount');

        $invoice->update([
            'total' => $total,
            'vat_total' => $vatTotal,
        ]);
    }

}
