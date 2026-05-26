<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Models\Appointment;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\Sale;
use App\Modules\POS\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function terminal(Request $request)
    {
        $appointment      = null;
        $preloadItems     = [];
        $serviceSuggestions = [];

        if ($request->filled('appointment')) {
            $appointment = Appointment::with(['customer', 'service.serviceProducts.product', 'staff', 'sale'])
                ->findOrFail($request->appointment);

            if ($appointment->sale) {
                return redirect()->route('pos.sales.show', $appointment->sale)
                    ->with('error', 'This appointment has already been checked out.');
            }

            $preloadItems[] = [
                'id'         => $appointment->service->id,
                'type'       => 'service',
                'name'       => $appointment->service->name,
                'unit_price' => (float) $appointment->service->price,
                'qty'        => 1,
                'subtotal'   => (float) $appointment->service->price,
            ];

            // Build product suggestions from linked service_products
            $serviceSuggestions = $appointment->service->serviceProducts
                ->filter(fn($sp) => $sp->product?->is_active)
                ->map(fn($sp) => [
                    'id'       => $sp->product->id,
                    'name'     => $sp->product->name,
                    'category' => $sp->product->category ?? 'General',
                    'price'    => (float) $sp->product->price,
                    'sku'      => $sp->product->sku,
                    'stock'    => $sp->product->stock_quantity,
                    'tracked'  => $sp->product->track_stock,
                ])
                ->values()
                ->all();
        }

        $products = Product::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(fn($p) => [
                'id'       => $p->id,
                'name'     => $p->name,
                'category' => $p->category ?? 'General',
                'price'    => (float) $p->price,
                'sku'      => $p->sku,
                'stock'    => $p->stock_quantity,
                'tracked'  => $p->track_stock,
            ]);

        return view('pos.terminal', compact('appointment', 'products', 'preloadItems', 'serviceSuggestions'));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'items'          => 'required|array|min:1',
            'items.*.type'   => 'required|in:service,product',
            'items.*.id'     => 'required|integer',
            'items.*.name'   => 'required|string',
            'items.*.price'  => 'required|numeric|min:0',
            'items.*.qty'    => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,card,eft,split',
            'discount'       => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string|max:500',
            'appointment_id' => 'nullable|exists:appointments,id',
            'customer_id'    => 'nullable|exists:customers,id',
        ]);

        DB::transaction(function () use ($request) {
            $items    = $request->items;
            $discount = (float) ($request->discount ?? 0);
            $subtotal = collect($items)->sum(fn($i) => $i['price'] * $i['qty']);
            $total    = max(0, $subtotal - $discount);

            $sale = Sale::create([
                'reference'       => Sale::generateReference(),
                'appointment_id'  => $request->appointment_id,
                'customer_id'     => $request->customer_id,
                'status'          => 'paid',
                'subtotal'        => $subtotal,
                'discount_amount' => $discount,
                'tax_amount'      => 0,
                'total'           => $total,
                'payment_method'  => $request->payment_method,
                'notes'           => $request->notes,
                'paid_at'         => now(),
            ]);

            foreach ($items as $item) {
                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'item_type'  => $item['type'],
                    'item_id'    => $item['id'],
                    'name'       => $item['name'],
                    'unit_price' => $item['price'],
                    'quantity'   => $item['qty'],
                    'subtotal'   => $item['price'] * $item['qty'],
                ]);

                // Decrement stock + write adjustment log for products
                if ($item['type'] === 'product') {
                    $product = Product::find($item['id']);
                    $product?->decrementStock($item['qty'], 'sale', [
                        'sale_id'   => $sale->id,
                        'reference' => $sale->reference,
                    ]);
                }
            }

            // Link appointment and mark completed
            if ($request->appointment_id) {
                Appointment::where('id', $request->appointment_id)->update([
                    'pos_order_id' => $sale->id,
                    'status'       => 'completed',
                ]);
            }

            session(['last_sale_id' => $sale->id]);
        });

        return redirect()->route('pos.sales.show', session('last_sale_id'))
            ->with('success', 'Payment processed successfully.');
    }
}
