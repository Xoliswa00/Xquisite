<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductItem;
use Illuminate\Http\Request;

class ProductItemsController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_included' => 'nullable|boolean',
            'price'       => 'nullable|numeric|min:0',
        ]);

        $item = $product->items()->create($validated);

        return back()->with('success', 'Item added.');
    }

    public function update(Request $request, Product $product, ProductItem $item)
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_included' => 'nullable|boolean',
            'price'       => 'nullable|numeric|min:0',
        ]);

        $item->update($validated);

        return back()->with('success', 'Item updated.');
    }

    public function destroy(Product $product, ProductItem $item)
    {
        $this->authorize('update', $product);
        $item->delete();
        return back()->with('success', 'Item removed.');
    }
}
