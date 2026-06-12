<?php

namespace App\Http\Controllers;

use App\Models\product_group;
use App\Http\Requests\Storeproduct_groupRequest;
use App\Http\Requests\Updateproduct_groupRequest;

class ProductGroupController extends Controller
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
    public function store(Storeproduct_groupRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(product_group $product_group)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(product_group $product_group)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Updateproduct_groupRequest $request, product_group $product_group)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(product_group $product_group)
    {
        //
    }
}
