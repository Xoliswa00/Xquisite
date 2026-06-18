<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\PaymentPlan;
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
            $appointment = Appointment::with(['customer', 'services.serviceProducts.product', 'staff', 'sale'])
                ->findOrFail($request->appointment);

            if ($appointment->sale) {
                return redirect()->route('pos.sales.show', $appointment->sale)
                    ->with('error', 'This appointment has already been checked out.');
            }

            foreach ($appointment->services as $service) {
                $preloadItems[] = [
                    'id'         => $service->id,
                    'type'       => 'service',
                    'name'       => $service->name,
                    'unit_price' => (float) ($service->pivot->price_at_booking ?? $service->price),
                    'qty'        => 1,
                    'subtotal'   => (float) ($service->pivot->price_at_booking ?? $service->price),
                ];
            }

            // Build product suggestions from all booked services' linked products
            $serviceSuggestions = $appointment->services
                ->flatMap(fn($service) => $service->serviceProducts)
                ->filter(fn($sp) => $sp->product?->is_active)
                ->unique(fn($sp) => $sp->product_id)
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

    public function layby(Request $request)
    {
        $request->validate([
            'items'                  => 'required|array|min:1',
            'items.*.type'           => 'required|in:service,product',
            'items.*.id'             => 'required|integer',
            'items.*.name'           => 'required|string',
            'items.*.price'          => 'required|numeric|min:0',
            'items.*.qty'            => 'required|integer|min:1',
            'deposit_amount'         => 'required|numeric|min:1',
            'remaining_installments' => 'required|integer|min:0|max:24',
            'interval_days'          => 'required|integer|min:7',
            'deposit_due'            => 'required|date|after_or_equal:today',
            'cancellation_fee'       => 'nullable|numeric|min:0',
            'customer_id'            => 'nullable|exists:customers,id',
            'notes'                  => 'nullable|string|max:500',
        ]);

        $plan = null;

        DB::transaction(function () use ($request, &$plan) {
            $items    = $request->items;
            $subtotal = collect($items)->sum(fn ($i) => $i['price'] * $i['qty']);

            // Create the sale as 'layby' — stock NOT deducted yet
            $sale = Sale::create([
                'reference'       => Sale::generateReference(),
                'customer_id'     => $request->customer_id,
                'status'          => 'layby',
                'subtotal'        => $subtotal,
                'discount_amount' => 0,
                'tax_amount'      => 0,
                'total'           => $subtotal,
                'payment_method'  => 'layby',
                'notes'           => $request->notes,
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
            }

            $title = $request->notes
                ? "Layby – {$request->notes}"
                : "Layby – " . collect($items)->pluck('name')->implode(', ');

            $plan = PaymentPlan::create([
                'tenant_id'        => auth()->user()->tenant_id,
                'customer_id'      => $request->customer_id,
                'title'            => $title,
                'total_amount'     => $subtotal,
                'cancellation_fee' => $request->cancellation_fee ?? 0,
                'type'             => 'layby',
                'plannable_type'   => Sale::class,
                'plannable_id'     => $sale->id,
            ]);

            $schedule = PaymentPlan::buildSchedule(
                $subtotal,
                $request->deposit_amount,
                $request->remaining_installments,
                $request->deposit_due,
                $request->interval_days
            );

            foreach ($schedule as $row) {
                $plan->installments()->create($row);
            }

            // Record the deposit as paid immediately
            $deposit = $plan->installments->first();
            $deposit->markPaid('cash');
        });

        return redirect()->route('payment-plans.show', $plan)
            ->with('success', 'Layby created and deposit recorded.');
    }
}
