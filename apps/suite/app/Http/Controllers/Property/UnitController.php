<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Modules\Property\Models\Property;
use App\Modules\Property\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Property $property)
    {
        $units = $property->units()->with('activeLease.renter')->orderBy('unit_number')->get();
        return view('property.units.index', compact('property', 'units'));
    }

    public function create(Property $property)
    {
        return view('property.units.create', compact('property'));
    }

    public function store(Request $request, Property $property)
    {
        $validated = $request->validate([
            'unit_number'    => 'required|string|max:50|unique:units,unit_number,NULL,id,property_id,' . $property->id,
            'type'           => 'required|in:apartment,studio,bachelor,townhouse,house,office,retail,warehouse,other',
            'floor'          => 'nullable|integer|min:0',
            'bedrooms'       => 'nullable|integer|min:0',
            'bathrooms'      => 'nullable|integer|min:0',
            'size_sqm'       => 'nullable|numeric|min:0',
            'monthly_rent'   => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string',
        ]);

        $validated['property_id'] = $property->id;
        $validated['status'] = 'vacant';

        Unit::create($validated);

        return redirect()->route('properties.units.index', $property)
            ->with('success', 'Unit added.');
    }

    public function show(Property $property, Unit $unit)
    {
        $unit->load(['activeLease.renter', 'maintenanceRequests', 'rentPayments' => fn($q) => $q->latest()->limit(12)]);
        return view('property.units.show', compact('property', 'unit'));
    }

    public function edit(Property $property, Unit $unit)
    {
        return view('property.units.edit', compact('property', 'unit'));
    }

    public function update(Request $request, Property $property, Unit $unit)
    {
        $validated = $request->validate([
            'unit_number'    => 'required|string|max:50|unique:units,unit_number,' . $unit->id . ',id,property_id,' . $property->id,
            'type'           => 'required|in:apartment,studio,bachelor,townhouse,house,office,retail,warehouse,other',
            'floor'          => 'nullable|integer|min:0',
            'bedrooms'       => 'nullable|integer|min:0',
            'bathrooms'      => 'nullable|integer|min:0',
            'size_sqm'       => 'nullable|numeric|min:0',
            'monthly_rent'   => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string',
        ]);

        $unit->update($validated);

        return redirect()->route('properties.units.show', [$property, $unit])->with('success', 'Unit updated.');
    }

    public function destroy(Property $property, Unit $unit)
    {
        abort_if($unit->status === 'occupied', 422, 'Cannot delete an occupied unit.');
        $unit->delete();
        return redirect()->route('properties.units.index', $property)->with('success', 'Unit removed.');
    }
}
