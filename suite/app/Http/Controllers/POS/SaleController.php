<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Models\Appointment;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\Sale;
use App\Modules\POS\Models\StockAdjustment;
use App\Modules\POS\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'items'])
            ->orderByDesc('paid_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        if ($request->filled('date')) {
            $query->whereDate('paid_at', $request->date);
        }

        $sales = $query->paginate(20)->withQueryString();

        $todayTotal = Sale::whereDate('paid_at', today())->where('status', 'paid')->sum('total');
        $todayCount = Sale::whereDate('paid_at', today())->where('status', 'paid')->count();

        return view('pos.sales.index', compact('sales', 'todayTotal', 'todayCount'));
    }

    public function show(Sale $sale)
    {
        $sale->load(['items', 'customer', 'appointment.staff']);

        return view('pos.sales.show', compact('sale'));
    }

    public function downloadPdf(Sale $sale)
    {
        $sale->load(['items', 'customer', 'appointment.staff']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pos.sales.receipt-pdf', ['sale' => $sale]);
        $pdf->getDomPDF()->addInfo('Title', "Receipt {$sale->reference}");
        $pdf->getDomPDF()->addInfo('Author', \App\Models\BillingSetting::get('company_name') ?: config('app.name'));

        return $pdf->download('receipt-' . $sale->reference . '.pdf');
    }

    public function void(Sale $sale)
    {
        if ($sale->status === 'paid') {
            DB::transaction(function () use ($sale) {
                $sale->load('items');

                foreach ($sale->items as $item) {
                    if ($item->item_type !== 'product') {
                        continue;
                    }

                    $product = Product::find($item->item_id);

                    if ($product && $product->track_stock) {
                        $this->inventory->increment($product, $item->quantity, StockAdjustment::TYPE_MANUAL_IN, [
                            'notes' => "Restored from voided sale {$sale->reference}",
                            'sale_id' => $sale->id,
                        ]);
                    }
                }

                $sale->update(['status' => 'voided']);

                // Unlink appointment
                if ($sale->appointment_id) {
                    Appointment::where('id', $sale->appointment_id)
                        ->update(['pos_order_id' => null, 'status' => 'confirmed']);
                }
            });
        }

        return redirect()->route('pos.sales.show', $sale)
            ->with('success', 'Sale voided.');
    }
}
