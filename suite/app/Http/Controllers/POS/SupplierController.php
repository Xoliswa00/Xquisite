<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Modules\POS\Models\Supplier;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\PurchaseOrder;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount(['products', 'purchaseOrders'])
            ->orderBy('name')
            ->get();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'website'        => 'nullable|url|max:255',
            'address'        => 'nullable|string|max:1000',
            'payment_terms'  => 'nullable|string|max:100',
            'notes'          => 'nullable|string|max:2000',
            'is_active'      => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        Supplier::create($data);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier added.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->loadCount(['products', 'purchaseOrders']);

        $products = Product::where('supplier_id', $supplier->id)
            ->orderBy('name')
            ->get();

        $orders = PurchaseOrder::where('supplier_id', $supplier->id)
            ->withCount('items')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('suppliers.show', compact('supplier', 'products', 'orders'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'website'        => 'nullable|url|max:255',
            'address'        => 'nullable|string|max:1000',
            'payment_terms'  => 'nullable|string|max:100',
            'notes'          => 'nullable|string|max:2000',
            'is_active'      => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $supplier->update($data);

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->update(['is_active' => false]);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deactivated.');
    }
}
