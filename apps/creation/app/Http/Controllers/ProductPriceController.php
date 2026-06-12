<?php

namespace App\Http\Controllers;

use App\Models\product_price;
use App\Http\Requests\Storeproduct_priceRequest;
use App\Http\Requests\Updateproduct_priceRequest;

class ProductPriceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Storeproduct_priceRequest $request)
    {
        $request->validate([
            'price' => 'required|numeric|min:0',
            'vat_rate' => 'required|numeric',
        ]);

        // Deactivate old prices
        $product->prices()->update([
            'is_active' => false,
            'effective_to' => now()
        ]);

        // Insert new price
        $product->prices()->create([
            'price' => $request->price,
            'vat_rate' => $request->vat_rate,
            'effective_from' => now(),
            'is_active' => true
        ]);

        return back()->with('success', 'New price set');
    }

    /**
     * Display the specified resource.
     */
    public function show(product_price $product_price)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(product_price $product_price)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Updateproduct_priceRequest $request, product_price $product_price)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(product_price $product_price)
    {
        //
    }
}
