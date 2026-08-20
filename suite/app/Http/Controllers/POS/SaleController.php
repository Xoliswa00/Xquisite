<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Modules\POS\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{
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

    public function void(Sale $sale)
    {
        if ($sale->status === 'paid') {
            $sale->update(['status' => 'voided']);

            // Unlink appointment
            if ($sale->appointment_id) {
                \App\Modules\Booking\Models\Appointment::where('id', $sale->appointment_id)
                    ->update(['pos_order_id' => null, 'status' => 'confirmed']);
            }
        }

        return redirect()->route('pos.sales.show', $sale)
            ->with('success', 'Sale voided.');
    }
}
