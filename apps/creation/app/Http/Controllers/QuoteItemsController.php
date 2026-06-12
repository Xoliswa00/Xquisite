<?php

namespace App\Http\Controllers;

use App\Models\quote_items;
use App\Http\Requests\Storequote_itemsRequest;
use App\Http\Requests\Updatequote_itemsRequest;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class QuoteItemsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
 public function store(Request $request, Quote $quote)
    {
        $this->authorize('update', $quote);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function() use ($quote, $validated) {

            $product = auth()->user()
                ->currentCompany
                ->products()
                ->findOrFail($validated['product_id']);

            $vat = $product->price * ($product->vat_rate / 100);
            $lineTotal = ($product->price + $vat) * $validated['quantity'];

            $item = $quote->items()->create([
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
                'unit_price' => $product->price,
                'vat_amount' => $vat,
                'total' => $lineTotal,
            ]);

            $this->recalculateQuote($quote);

            return $item;
        });
    }

    public function update(Request $request, quote_items $quoteItem)
    {
        $quote = $quoteItem->quote;
        $this->authorize('update', $quote);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function() use ($quoteItem, $validated, $quote) {

            $vat = $quoteItem->unit_price * 
                ($quoteItem->product->vat_rate / 100);

            $lineTotal = ($quoteItem->unit_price + $vat) 
                * $validated['quantity'];

            $quoteItem->update([
                'quantity' => $validated['quantity'],
                'vat_amount' => $vat,
                'total' => $lineTotal,
            ]);

            $this->recalculateQuote($quote);

            return $quoteItem;
        });
    }

    public function destroy(quote_items $quoteItem)
    {
        $quote = $quoteItem->quote;
        $this->authorize('update', $quote);

        DB::transaction(function() use ($quoteItem, $quote) {
            $quoteItem->delete();
            $this->recalculateQuote($quote);
        });

        return response()->noContent();
    }

    private function recalculateQuote(Quote $quote)
    {
        $total = $quote->items()->sum('total');
        $quote->update(['total' => $total]);
    }
}
