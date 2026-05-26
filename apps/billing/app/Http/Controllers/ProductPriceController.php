<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Http\Request;

class ProductPriceController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'pricing_type' => 'required|in:fixed,hourly,range,per_item',
            'price'        => 'nullable|numeric|min:0',
            'min_price'    => 'nullable|numeric|min:0',
            'max_price'    => 'nullable|numeric|min:0',
            'vat_rate'     => 'nullable|numeric|min:0|max:100',
        ]);

        // Deactivate existing active prices
        $product->pricing()->where('is_active', true)->update([
            'is_active'    => false,
            'effective_to' => now(),
        ]);

        $product->pricing()->create([
            'pricing_type'   => $validated['pricing_type'],
            'price'          => $validated['price'] ?? null,
            'min_price'      => $validated['min_price'] ?? null,
            'max_price'      => $validated['max_price'] ?? null,
            'vat_rate'       => $validated['vat_rate'] ?? 15,
            'is_active'      => true,
            'effective_from' => now(),
        ]);

        return back()->with('success', 'Price updated.');
    }

    public function destroy(Product $product, ProductPrice $price)
    {
        $this->authorize('update', $product);
        $price->delete();
        return back()->with('success', 'Price removed.');
    }
}
