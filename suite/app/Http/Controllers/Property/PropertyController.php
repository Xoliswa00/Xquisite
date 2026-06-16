<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Modules\Property\Models\Property;
use App\Modules\Property\Models\Unit;
use App\Modules\Property\Models\Lease;
use App\Modules\Property\Models\RentPayment;
use App\Modules\Property\Models\MaintenanceRequest;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index()
    {
        $properties = Property::withCount(['units', 'occupiedUnits', 'vacantUnits'])
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(20);

        $stats = [
            'total_properties' => Property::where('is_active', true)->count(),
            'total_units'      => Unit::count(),
            'occupied'         => Unit::where('status', 'occupied')->count(),
            'vacant'           => Unit::where('status', 'vacant')->count(),
            'overdue_payments' => RentPayment::where('status', 'overdue')->count(),
            'open_maintenance' => MaintenanceRequest::whereIn('status', ['open', 'in_progress'])->count(),
        ];

        return view('property.properties.index', compact('properties', 'stats'));
    }

    public function create()
    {
        return view('property.properties.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'address_line_1'=> 'required|string|max:255',
            'address_line_2'=> 'nullable|string|max:255',
            'city'          => 'required|string|max:100',
            'province'      => 'nullable|string|max:100',
            'postal_code'   => 'nullable|string|max:20',
            'country'       => 'nullable|string|max:100',
            'type'          => 'required|in:residential,commercial,mixed,industrial',
            'description'   => 'nullable|string',
            'owner_name'    => 'nullable|string|max:255',
            'owner_email'   => 'nullable|email|max:255',
            'owner_phone'   => 'nullable|string|max:30',
        ]);

        $property = Property::create($validated);

        return redirect()->route('properties.show', $property)
            ->with('success', 'Property created.');
    }

    public function show(Property $property)
    {
        $property->load(['units.activeLease.renter']);

        $stats = [
            'units'      => $property->units()->count(),
            'occupied'   => $property->units()->where('status', 'occupied')->count(),
            'vacant'     => $property->units()->where('status', 'vacant')->count(),
            'maintenance'=> $property->maintenanceRequests()->whereIn('status', ['open', 'in_progress'])->count(),
            'monthly_rent'=> $property->units()->where('status', 'occupied')->sum('monthly_rent'),
        ];

        return view('property.properties.show', compact('property', 'stats'));
    }

    public function edit(Property $property)
    {
        return view('property.properties.edit', compact('property'));
    }

    public function update(Request $request, Property $property)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'address_line_1'=> 'required|string|max:255',
            'address_line_2'=> 'nullable|string|max:255',
            'city'          => 'required|string|max:100',
            'province'      => 'nullable|string|max:100',
            'postal_code'   => 'nullable|string|max:20',
            'country'       => 'nullable|string|max:100',
            'type'          => 'required|in:residential,commercial,mixed,industrial',
            'description'   => 'nullable|string',
            'owner_name'    => 'nullable|string|max:255',
            'owner_email'   => 'nullable|email|max:255',
            'owner_phone'   => 'nullable|string|max:30',
            'is_active'     => 'boolean',
        ]);

        $property->update($validated);

        return redirect()->route('properties.show', $property)->with('success', 'Property updated.');
    }

    public function destroy(Property $property)
    {
        $property->update(['is_active' => false]);
        return redirect()->route('properties.index')->with('success', 'Property deactivated.');
    }
}
