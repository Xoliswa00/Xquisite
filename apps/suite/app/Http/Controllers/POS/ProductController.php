<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Modules\POS\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::orderBy('category')->orderBy('name');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%');
            });
        }

        $products = $query->paginate(20)->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'sku'              => 'nullable|string|max:100',
            'category'         => 'nullable|string|max:100',
            'description'      => 'nullable|string|max:2000',
            'price'            => 'required|numeric|min:0',
            'cost_price'       => 'nullable|numeric|min:0',
            'stock_quantity'   => 'required|integer|min:0',
            'track_stock'      => 'boolean',
            'is_active'        => 'boolean',
            'reorder_level'    => 'nullable|integer|min:0',
            'reorder_quantity' => 'nullable|integer|min:0',
            'supplier'              => 'nullable|string|max:255',
            'supplier_sku'          => 'nullable|string|max:100',
            'image_url'             => 'nullable|url|max:500',
            'is_available_online'   => 'boolean',
        ]);

        $data['track_stock']         = $request->boolean('track_stock');
        $data['is_active']           = $request->boolean('is_active', true);
        $data['is_available_online'] = $request->boolean('is_available_online');

        Product::create($data);

        return redirect()->route('products.index')
            ->with('success', 'Product added.');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'sku'              => 'nullable|string|max:100',
            'category'         => 'nullable|string|max:100',
            'description'      => 'nullable|string|max:2000',
            'price'            => 'required|numeric|min:0',
            'cost_price'       => 'nullable|numeric|min:0',
            'stock_quantity'   => 'required|integer|min:0',
            'track_stock'      => 'boolean',
            'is_active'        => 'boolean',
            'reorder_level'    => 'nullable|integer|min:0',
            'reorder_quantity' => 'nullable|integer|min:0',
            'supplier'              => 'nullable|string|max:255',
            'supplier_sku'          => 'nullable|string|max:100',
            'image_url'             => 'nullable|url|max:500',
            'is_available_online'   => 'boolean',
        ]);

        $data['track_stock']         = $request->boolean('track_stock');
        $data['is_active']           = $request->boolean('is_active', true);
        $data['is_available_online'] = $request->boolean('is_available_online');

        $product->update($data);

        return redirect()->route('products.index')
            ->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product removed.');
    }
}
