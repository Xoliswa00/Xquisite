<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    /**
     * Stock take portal — show all tracked products with current vs physical count.
     */
    public function takePage()
    {
        $products = Product::where('track_stock', true)
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('stock.take', compact('products'));
    }

    /**
     * Save a stock take — adjust each product to the physical count entered.
     */
    public function saveStockTake(Request $request)
    {
        $request->validate([
            'counts'         => 'required|array',
            'counts.*'       => 'nullable|integer|min:0',
            'notes'          => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->counts as $productId => $physicalCount) {
                if ($physicalCount === null || $physicalCount === '') continue;

                $product = Product::find($productId);
                if (!$product || !$product->track_stock) continue;

                $product->adjustToCount((int) $physicalCount, $request->notes ?? 'Stock take');
            }
        });

        return redirect()->route('stock.take')
            ->with('success', 'Stock take saved. All levels updated.');
    }

    /**
     * Manual adjustment — add or remove stock for a single product.
     */
    public function adjust(Request $request, Product $product)
    {
        $data = $request->validate([
            'type'     => 'required|in:adjustment_in,adjustment_out',
            'quantity' => 'required|integer|min:1',
            'notes'    => 'nullable|string|max:500',
        ]);

        if ($data['type'] === 'adjustment_in') {
            $product->incrementStock($data['quantity'], StockAdjustment::TYPE_MANUAL_IN, [
                'notes' => $data['notes'],
            ]);
        } else {
            $product->decrementStock($data['quantity'], StockAdjustment::TYPE_MANUAL_OUT, [
                'notes' => $data['notes'],
            ]);
        }

        return back()->with('success', 'Stock adjusted.');
    }

    /**
     * Full movement history for a single product.
     */
    public function history(Product $product)
    {
        $adjustments = $product->stockAdjustments()->paginate(25);

        return view('stock.history', compact('product', 'adjustments'));
    }

    /**
     * Reorder alerts — products at or below reorder_level.
     */
    public function reorderAlerts()
    {
        $products = Product::where('track_stock', true)
            ->where('reorder_level', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'reorder_level')
            ->orderBy('stock_quantity')
            ->get();

        return view('stock.reorder-alerts', compact('products'));
    }
}
