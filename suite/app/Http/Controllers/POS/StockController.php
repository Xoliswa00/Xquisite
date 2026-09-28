<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\ProductVariant;
use App\Modules\POS\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    /**
     * Stock take portal — one row per tracked, active product or variant. A
     * has_variants product's own stock_quantity isn't authoritative once it
     * has variants, so it contributes rows via its variants, not itself.
     */
    public function takePage()
    {
        $rows = $this->trackedStockRows();

        return view('stock.take', compact('rows'));
    }

    /**
     * Save a stock take — adjust each product/variant to the physical count
     * entered. Form fields are prefixed p_{id}/v_{id} (same convention the
     * storefront cart uses to disambiguate a plain product from a variant
     * sharing the same numeric ID space).
     */
    public function saveStockTake(Request $request)
    {
        $request->validate([
            'counts'   => 'required|array',
            'counts.*' => 'nullable|integer|min:0',
            'notes'    => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->counts as $key => $physicalCount) {
                if ($physicalCount === null || $physicalCount === '') continue;

                if (str_starts_with($key, 'v_')) {
                    $variant = ProductVariant::find(substr($key, 2));
                    if (!$variant || !$variant->track_stock) continue;

                    $variant->adjustToCount((int) $physicalCount, $request->notes ?? 'Stock take');
                    continue;
                }

                $productId = str_starts_with($key, 'p_') ? substr($key, 2) : $key;
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

    /** Same as adjust(), for a single variant. */
    public function adjustVariant(Request $request, Product $product, ProductVariant $variant)
    {
        abort_unless($variant->product_id === $product->id, 404);

        $data = $request->validate([
            'type'     => 'required|in:adjustment_in,adjustment_out',
            'quantity' => 'required|integer|min:1',
            'notes'    => 'nullable|string|max:500',
        ]);

        if ($data['type'] === 'adjustment_in') {
            $variant->incrementStock($data['quantity'], StockAdjustment::TYPE_MANUAL_IN, [
                'notes' => $data['notes'],
            ]);
        } else {
            $variant->decrementStock($data['quantity'], StockAdjustment::TYPE_MANUAL_OUT, [
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
     * Reorder alerts — products (and variants) at or below their reorder
     * level. A has_variants product's own reorder_level/stock_quantity
     * isn't checked directly — its variants are, individually, since
     * demand (and so the right reorder point) genuinely differs per
     * size/color.
     */
    public function reorderAlerts()
    {
        $rows = $this->trackedStockRows()->filter(fn ($row) => $row->needs_reorder)->values();

        return view('stock.reorder-alerts', compact('rows'));
    }

    /**
     * Normalizes tracked, active products (has_variants: false) and tracked,
     * active variants (has_variants: true, one row per variant) into one
     * flat, view-friendly row list — stock.take and reorder-alerts both need
     * "every stock-tracked thing, whether it's a plain product or a
     * variant" and would otherwise need two near-duplicate table bodies /
     * query blocks each.
     */
    private function trackedStockRows(): Collection
    {
        $products = Product::where('track_stock', true)
            ->where('is_active', true)
            ->where('has_variants', false)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(fn (Product $p) => (object) [
                'form_key'         => "p_{$p->id}",
                'name'             => $p->name,
                'sku'              => $p->sku,
                'category'         => $p->category,
                'stock_quantity'   => $p->stock_quantity,
                'reorder_level'    => $p->reorder_level,
                'reorder_quantity' => $p->reorder_quantity,
                'supplier'         => $p->supplier,
                'stock_status'     => $p->stock_status,
                'needs_reorder'    => $p->needs_reorder,
                'history_url'      => route('stock.history', $p),
                'adjust_url'       => route('stock.adjust', $p),
            ]);

        $variants = ProductVariant::query()
            ->where('track_stock', true)
            ->where('is_active', true)
            ->whereHas('product', fn ($q) => $q->where('is_active', true))
            ->with('product')
            ->get()
            ->map(fn (ProductVariant $v) => (object) [
                'form_key'         => "v_{$v->id}",
                'name'             => "{$v->product->name} — {$v->label}",
                'sku'              => $v->sku ?: $v->product->sku,
                'category'         => $v->product->category,
                'stock_quantity'   => $v->stock_quantity,
                'reorder_level'    => $v->effectiveReorderLevel(),
                'reorder_quantity' => $v->effectiveReorderQuantity(),
                'supplier'         => $v->product->supplier,
                'stock_status'     => $v->stock_status,
                'needs_reorder'    => $v->needs_reorder,
                'history_url'      => null, // variant-level history isn't built yet — see PR follow-up note
                'adjust_url'       => route('stock.variant.adjust', [$v->product, $v]),
            ]);

        return $products->concat($variants)->sortBy('name')->values();
    }
}
