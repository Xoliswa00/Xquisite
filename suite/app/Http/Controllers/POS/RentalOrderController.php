<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\RentalOrder;
use App\Modules\Booking\Models\Customer;
use App\Modules\POS\Models\Product;
use Illuminate\Http\Request;

class RentalOrderController extends Controller
{
    public function index()
    {
        $orders = RentalOrder::where('tenant_id', auth()->user()->tenant_id)
            ->with(['product', 'customer'])
            ->latest()
            ->paginate(30);

        $overdueCount = RentalOrder::where('tenant_id', auth()->user()->tenant_id)->overdue()->count();

        return view('rental-orders.index', compact('orders', 'overdueCount'));
    }

    public function create()
    {
        $products  = Product::where('is_active', true)->where('is_rentable', true)->orderBy('name')->get();
        $customers = Customer::where('is_active', true)->orderBy('name')->get();

        return view('rental-orders.create', compact('products', 'customers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'    => ['required', 'exists:products,id', function ($attr, $value, $fail) {
                if (! \App\Modules\POS\Models\Product::where('id', $value)->where('is_rentable', true)->exists()) {
                    $fail('This product is not set up for rental.');
                }
            }],
            'customer_id'   => 'nullable|exists:customers,id',
            'appointment_id'=> 'nullable|exists:appointments,id',
            'quantity'      => 'required|integer|min:1',
            'event_date'    => 'required|date',
            'return_due_at' => 'required|date|after_or_equal:event_date',
            'notes'         => 'nullable|string|max:1000',
        ]);

        $product = Product::findOrFail($data['product_id']);

        // Availability check
        $available = $product->unitsAvailable($data['event_date']);
        if ($available < $data['quantity']) {
            return back()->withInput()->withErrors([
                'quantity' => "Only {$available} unit(s) available on " . $data['event_date'] . ".",
            ]);
        }

        RentalOrder::create([
            ...$data,
            'tenant_id'   => auth()->user()->tenant_id,
            'rental_rate' => $product->rental_rate,
        ]);

        return redirect()->route('rental-orders.index')
            ->with('success', "Rental order created for {$product->name}.");
    }

    public function show(RentalOrder $rentalOrder)
    {
        $this->authorise($rentalOrder);
        $rentalOrder->load(['product', 'customer', 'appointment']);

        return view('rental-orders.show', compact('rentalOrder'));
    }

    public function markOut(RentalOrder $rentalOrder)
    {
        $this->authorise($rentalOrder);
        $rentalOrder->update(['status' => 'out']);

        return back()->with('success', 'Marked as out for event.');
    }

    public function returnItem(Request $request, RentalOrder $rentalOrder)
    {
        $this->authorise($rentalOrder);

        $data = $request->validate([
            'condition_on_return' => 'required|in:excellent,good,fair,damaged',
        ]);

        $rentalOrder->markReturned($data['condition_on_return']);

        $msg = $rentalOrder->status === 'damaged'
            ? 'Item returned as damaged — please follow up on repair costs.'
            : 'Item returned successfully.';

        return back()->with('success', $msg);
    }

    private function authorise(RentalOrder $order): void
    {
        abort_unless($order->tenant_id === auth()->user()->tenant_id, 403);
    }
}
