<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Modules\Property\Models\LeaseCharge;
use App\Modules\Property\Models\Property;
use App\Modules\Property\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

    public function bulkCreate(Property $property)
    {
        return view('property.units.bulk-create', compact('property'));
    }

    /** Create a run of identical units (e.g. 301-320) in one submit — all validated up front so it's all-or-nothing, not a partial batch. */
    public function bulkStore(Request $request, Property $property)
    {
        $validated = $request->validate([
            'start_number'   => 'required|integer|min:0',
            'count'          => 'required|integer|min:1|max:100',
            'type'           => 'required|in:apartment,studio,bachelor,townhouse,house,office,retail,warehouse,other',
            'floor'          => 'nullable|integer|min:0',
            'bedrooms'       => 'nullable|integer|min:0',
            'bathrooms'      => 'nullable|integer|min:0',
            'size_sqm'       => 'nullable|numeric|min:0',
            'monthly_rent'   => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string',
        ]);

        $unitNumbers = range($validated['start_number'], $validated['start_number'] + $validated['count'] - 1);

        $existing = $property->units()->whereIn('unit_number', $unitNumbers)->pluck('unit_number');
        if ($existing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'start_number' => 'These unit numbers already exist on this property: ' . $existing->implode(', '),
            ]);
        }

        $common = [
            'property_id'    => $property->id,
            'type'           => $validated['type'],
            'floor'          => $validated['floor'] ?? null,
            'bedrooms'       => $validated['bedrooms'] ?? null,
            'bathrooms'      => $validated['bathrooms'] ?? null,
            'size_sqm'       => $validated['size_sqm'] ?? null,
            'monthly_rent'   => $validated['monthly_rent'],
            'deposit_amount' => $validated['deposit_amount'] ?? 0,
            'notes'          => $validated['notes'] ?? null,
            'status'         => 'vacant',
        ];

        DB::transaction(function () use ($unitNumbers, $common) {
            foreach ($unitNumbers as $number) {
                Unit::create(['unit_number' => (string) $number] + $common);
            }
        });

        return redirect()->route('properties.units.index', $property)
            ->with('success', count($unitNumbers) . ' units added: ' . reset($unitNumbers) . '–' . end($unitNumbers) . '.');
    }

    // Feeds the Lease/Maintenance "New" forms' unit dropdown, which is
    // populated client-side once a property is selected.
    public function apiIndex(Property $property)
    {
        $units = $property->units()->orderBy('unit_number')->get(['id', 'unit_number', 'monthly_rent', 'status']);
        return response()->json($units);
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
            'parking_bay'    => 'nullable|string|max:50',
            'monthly_rent'   => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string',
        ]);

        $validated['property_id'] = $property->id;
        $validated['status'] = 'vacant';
        $validated['deposit_amount'] = $validated['deposit_amount'] ?? 0;

        Unit::create($validated);

        return redirect()->route('properties.units.index', $property)
            ->with('success', 'Unit added.');
    }

    public function show(Property $property, Unit $unit)
    {
        $unit->load([
            'activeLease.renter',
            'maintenanceRequests',
            'rentPayments' => fn($q) => $q->latest()->limit(12),
            'expenses' => fn($q) => $q->latest('date'),
        ]);

        $chargeIncome = LeaseCharge::whereHas('lease', fn ($q) => $q->where('unit_id', $unit->id))->sum('amount_paid');

        $recon = [
            'income'   => (float) $unit->rentPayments()->sum('amount_paid') + (float) $chargeIncome,
            'expenses' => (float) $unit->expenses()->sum('amount'),
        ];
        $recon['net'] = $recon['income'] - $recon['expenses'];

        return view('property.units.show', compact('property', 'unit', 'recon'));
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
            'parking_bay'    => 'nullable|string|max:50',
            'monthly_rent'   => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string',
        ]);

        $validated['deposit_amount'] = $validated['deposit_amount'] ?? 0;

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
