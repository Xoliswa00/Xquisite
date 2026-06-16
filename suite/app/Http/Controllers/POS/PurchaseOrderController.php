<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Modules\POS\Models\Product;
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
        $preloadProducts = collect();
        if ($request->filled('from_reorder')) {
            $preloadProducts = Product::where('track_stock', true)
                ->where('reorder_level', '>', 0)
                ->whereColumn('stock_quantity', '<=', 'reorder_level')
                ->get();
        }

        $allProducts = Product::where('is_active', true)->orderBy('name')->get();
        $suppliers   = Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('purchase-orders.create', compact('allProducts', 'preloadProducts', 'suppliers'));
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
                $subtotal = $item['qty'] * $item['unit_cost'];
                $total   += $subtotal;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id'        => $item['product_id'],
                    'product_name'      => $product->name,
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
        $purchaseOrder->load('items.product');

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

                // Increment product stock
                $item->product->incrementStock($qty, StockAdjustment::TYPE_RECEIVE, [
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
