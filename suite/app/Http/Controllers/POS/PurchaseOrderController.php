<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\ProductVariant;
use App\Modules\POS\Models\PurchaseOrder;
use App\Modules\POS\Models\PurchaseOrderItem;
use App\Modules\POS\Models\StockAdjustment;
use App\Modules\POS\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $orders = PurchaseOrder::withCount('items')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('purchase-orders.index', compact('orders'));
    }

    public function create(Request $request)
    {
        // Unified preload rows for "Create PO for All" from the reorder
        // alerts page — a plain {product_id, variant_id, qty, unit_cost}
        // shape so the create form doesn't need to know a row came from a
        // product or a variant. A has_variants product's own
        // stock_quantity/reorder_level aren't authoritative, so it's
        // excluded from the product half and covered by the variant half
        // instead — same split as everywhere else variants touch stock.
        $preloadItems = collect();
        if ($request->filled('from_reorder')) {
            $lowProducts = Product::where('track_stock', true)
                ->where('has_variants', false)
                ->where('reorder_level', '>', 0)
                ->whereColumn('stock_quantity', '<=', 'reorder_level')
                ->get()
                ->map(function (Product $p) {
                    $qty = max(1, (int) $p->reorder_quantity);
                    $unitCost = (float) $p->cost_price;

                    return [
                        'product_id' => $p->id,
                        'variant_id' => '',
                        'qty'        => $qty,
                        'unit_cost'  => $unitCost,
                        'subtotal'   => $qty * $unitCost,
                    ];
                });

            $lowVariants = ProductVariant::where('track_stock', true)
                ->where('is_active', true)
                ->with('product')
                ->get()
                ->filter(fn (ProductVariant $v) => $v->needs_reorder)
                ->map(function (ProductVariant $v) {
                    $qty = max(1, $v->effectiveReorderQuantity());
                    $unitCost = (float) ($v->product->cost_price ?? 0);

                    return [
                        'product_id' => $v->product_id,
                        'variant_id' => $v->id,
                        'qty'        => $qty,
                        'unit_cost'  => $unitCost,
                        'subtotal'   => $qty * $unitCost,
                    ];
                });

            // Pre-resolved plain array, not remapped again in the view — a
            // simple, un-nested @json($collection->map(fn($x) => [...])) in
            // this Blade template's <script> block was ALSO enough to
            // confuse Blade's directive-argument parser into a ParseError
            // (not just the nested case above); passing an already-shaped
            // array sidesteps that entirely rather than relying on exactly
            // how simple a given @json(...->map(...)) has to stay to be safe.
            $preloadItems = $lowProducts->concat($lowVariants)->values();
        }

        $allProducts = Product::where('is_active', true)
            ->orderBy('name')
            ->with(['activeVariants' => fn ($q) => $q->orderBy('id')])
            ->get();

        // Pre-resolved to a plain nested array here, not built inline in the
        // Blade template's @json(...) — a deeply nested map-of-maps of
        // arrow-function closures inside a single directive call confused
        // Blade's directive-argument parser into cutting the expression off
        // mid-array, producing a ParseError ("'[' does not match ']'") on
        // every load of this page. Passing an already-resolved plain array
        // sidesteps that class of fragility entirely.
        $productsForJs = $allProducts->map(fn (Product $p) => [
            'id'               => $p->id,
            'name'             => $p->name,
            'cost_price'       => (float) $p->cost_price,
            'reorder_quantity' => (int) $p->reorder_quantity,
            'has_variants'     => (bool) $p->has_variants,
            'variants'         => $p->activeVariants->map(fn (ProductVariant $v) => [
                'id'               => $v->id,
                'label'            => $v->label,
                'reorder_quantity' => $v->effectiveReorderQuantity(),
            ])->values(),
        ])->values();

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('purchase-orders.create', compact('productsForJs', 'preloadItems', 'suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'       => 'nullable|exists:suppliers,id',
            'supplier'          => 'nullable|string|max:255',
            'supplier_contact'  => 'nullable|string|max:255',
            'notes'             => 'nullable|string|max:1000',
            'items'             => 'required|array|min:1',
            'items.*.product_id'=> 'required|exists:products,id',
            'items.*.variant_id'=> 'nullable|integer',
            'items.*.qty'       => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $supplierName = $request->supplier;
            if ($request->filled('supplier_id')) {
                $supplierName = Supplier::find($request->supplier_id)?->name ?? $supplierName;
            }

            $po = PurchaseOrder::create([
                'reference'        => PurchaseOrder::generateReference(),
                'supplier_id'      => $request->supplier_id,
                'supplier'         => $supplierName,
                'supplier_contact' => $request->supplier_contact,
                'status'           => PurchaseOrder::STATUS_DRAFT,
                'notes'            => $request->notes,
                'created_by'       => auth()->id(),
            ]);

            $total = 0;
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                // A has_variants product can't be ordered without picking a
                // variant — the create form always sends variant_id for
                // these, so a missing one here means a tampered/stale
                // request, not a real gap.
                $variant = null;
                if ($product->has_variants) {
                    $variant = ProductVariant::where('product_id', $product->id)
                        ->where('id', $item['variant_id'] ?? null)
                        ->firstOrFail();
                }

                $subtotal = $item['qty'] * $item['unit_cost'];
                $total   += $subtotal;

                PurchaseOrderItem::create([
                    'purchase_order_id'  => $po->id,
                    'product_id'         => $item['product_id'],
                    'product_variant_id' => $variant?->id,
                    'variant_attributes' => $variant?->attributes,
                    'product_name'       => $variant ? "{$product->name} — {$variant->label}" : $product->name,
                    'quantity_ordered'  => $item['qty'],
                    'quantity_received' => 0,
                    'unit_cost'         => $item['unit_cost'],
                    'subtotal'          => $subtotal,
                ]);
            }

            $po->update(['total_cost' => $total]);
            session(['last_po_id' => $po->id]);
        });

        return redirect()->route('purchase-orders.show', session('last_po_id'))
            ->with('success', 'Purchase order created.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('items.product', 'items.productVariant');

        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    /**
     * Mark PO as sent to supplier.
     */
    public function send(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === PurchaseOrder::STATUS_DRAFT) {
            $purchaseOrder->update([
                'status'  => PurchaseOrder::STATUS_SENT,
                'sent_at' => now(),
            ]);
        }

        return back()->with('success', 'Purchase order marked as sent.');
    }

    /**
     * Receive stock — update quantities received and adjust product stock levels.
     */
    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'received'    => 'required|array',
            'received.*'  => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $purchaseOrder) {
            $allReceived = true;

            foreach ($purchaseOrder->items as $item) {
                $qty = (int) ($request->received[$item->id] ?? 0);
                if ($qty <= 0) { $allReceived = false; continue; }

                $item->update([
                    'quantity_received' => $item->quantity_received + $qty,
                ]);

                // Increment the specific variant's stock if this line was
                // for one, otherwise the plain product's — same split as
                // every other stock-mutating flow (checkout, POS sale).
                $target = $item->product_variant_id ? $item->productVariant : $item->product;
                $target?->incrementStock($qty, StockAdjustment::TYPE_RECEIVE, [
                    'purchase_order_id' => $purchaseOrder->id,
                    'reference'         => $purchaseOrder->reference,
                    'notes'             => "Received on PO {$purchaseOrder->reference}",
                ]);

                if ($item->quantity_received < $item->quantity_ordered) {
                    $allReceived = false;
                }
            }

            $purchaseOrder->update([
                'status'      => $allReceived ? PurchaseOrder::STATUS_RECEIVED : PurchaseOrder::STATUS_PARTIAL,
                'received_at' => $allReceived ? now() : $purchaseOrder->received_at,
            ]);
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Stock received and levels updated.');
    }

    /**
     * Cancel a draft or sent PO.
     */
    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if (in_array($purchaseOrder->status, [PurchaseOrder::STATUS_DRAFT, PurchaseOrder::STATUS_SENT])) {
            $purchaseOrder->update(['status' => PurchaseOrder::STATUS_CANCELLED]);
        }

        return back()->with('success', 'Purchase order cancelled.');
    }
}
