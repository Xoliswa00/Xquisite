<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\PaymentPlan;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Ecommerce\Exceptions\InsufficientStockException;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\Sale;
use App\Modules\POS\Models\SaleItem;
use App\Modules\POS\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PosController extends Controller
{
    public function __construct(private readonly SaleService $sales) {}

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
                $qty            = $service->pivot->quantity ?? 1;
                $priceAtBooking = (float) ($service->pivot->price_at_booking ?? $service->calculatePrice($qty));

                $preloadItems[] = [
                    'id'         => $service->id,
                    'type'       => 'service',
                    'name'       => $service->name,
                    'unit_price' => $qty > 0 ? $priceAtBooking / $qty : $priceAtBooking,
                    'qty'        => $qty,
                    'subtotal'   => $priceAtBooking,
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

        $tenantId = auth()->user()->tenant_id;

        return view('pos.terminal', compact('appointment', 'products', 'preloadItems', 'serviceSuggestions', 'tenantId'));
    }

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'idempotency_key' => 'required|uuid',
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

        $tenant = auth()->user()->tenant;

        try {
            $sale = $this->sales->checkout($tenant, $data, $data['idempotency_key']);
        } catch (InsufficientStockException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('POS checkout failed', ['tenant' => $tenant->id, 'error' => $e->getMessage()]);

            return response()->json([
                'error' => 'We could not process this sale. No stock was deducted — please try again.',
            ], 500);
        }

        return response()->json([
            'sale_id'     => $sale->id,
            'reference'   => $sale->reference,
            'receipt_url' => route('pos.sales.show', $sale),
        ]);
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

            $sale->assignSequentialReference();

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
