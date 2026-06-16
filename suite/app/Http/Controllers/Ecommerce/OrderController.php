<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Modules\Ecommerce\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['items'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $orders = $query->paginate(20)->withQueryString();

        $todayTotal = Order::whereDate('created_at', today())->where('payment_status', 'paid')->sum('total');
        $todayCount = Order::whereDate('created_at', today())->count();
        $pendingCount = Order::whereIn('status', ['pending', 'processing'])->count();

        return view('orders.index', compact('orders', 'todayTotal', 'todayCount', 'pendingCount'));
    }

    public function show(Order $order)
    {
        $order->load('items');
        return view('orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,processing,ready,shipped,delivered,cancelled,refunded',
        ]);

        $data = ['status' => $request->status];

        if ($request->status === 'delivered') {
            $data['fulfilled_at'] = now();
        }

        $order->update($data);

        return back()->with('success', 'Order status updated to ' . ucfirst($request->status) . '.');
    }
}
