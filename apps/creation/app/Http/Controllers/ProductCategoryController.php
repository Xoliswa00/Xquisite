<?php

namespace App\Http\Controllers;

use App\Models\product_category;
use App\Http\Requests\Storeproduct_categoryRequest;
use App\Http\Requests\Updateproduct_categoryRequest;

class ProductCategoryController extends Controller
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
    public function store(Storeproduct_categoryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(product_category $product_category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(product_category $product_category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Updateproduct_categoryRequest $request, product_category $product_category)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(product_category $product_category)
    {
        //
    }
}
