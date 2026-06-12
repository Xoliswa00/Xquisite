<?php

namespace App\Http\Controllers;

use App\Models\product_items;
use Illuminate\Http\Request;

class ProductItemsController extends Controller
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
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $product->items()->create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Item added');
    }

    /**
     * Display the specified resource.
     */
    public function show(product_items $product_items)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(product_items $product_items)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, product_items $product_items)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(product_items $product_items)
    {
        //
    }
}
