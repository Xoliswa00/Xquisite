<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Modules\Property\Models\Lease;
use App\Modules\Property\Models\Property;
use App\Modules\Property\Models\Renter;
use App\Modules\Property\Models\Unit;
use App\Services\BillingBridge;
use Illuminate\Http\Request;

class LeaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Lease::with(['property', 'unit', 'renter'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        $leases     = $query->paginate(20)->withQueryString();
        $properties = Property::where('is_active', true)->orderBy('name')->get();

        return view('property.leases.index', compact('leases', 'properties'));
    }

    public function create(Request $request)
    {
        $properties = Property::where('is_active', true)->orderBy('name')->get();
        $renters    = Renter::orderBy('name')->get();

        $preUnit = $request->filled('unit_id') ? Unit::find($request->unit_id) : null;

        return view('property.leases.create', compact('properties', 'renters', 'preUnit'));
    }

    public function store(Request $request, BillingBridge $billing)
    {
        $validated = $request->validate([
            'property_id'    => 'required|exists:properties,id',
            'unit_id'        => 'required|exists:units,id',
            'renter_id'      => 'required|exists:renters,id',
            'start_date'     => 'required|date',
            'end_date'       => 'nullable|date|after:start_date',
            'monthly_rent'   => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'deposit_paid'   => 'boolean',
            'notes'          => 'nullable|string',
        ]);

        // Ensure unit is vacant
        $unit = Unit::findOrFail($validated['unit_id']);
        abort_if(!$unit->isVacant(), 422, 'This unit is not available.');

        // Ensure no active lease for this unit
        abort_if(
            Lease::where('unit_id', $unit->id)->where('status', 'active')->exists(),
            422, 'This unit already has an active lease.'
        );

        $validated['status'] = 'active';
        $lease = Lease::create($validated);

        // Mark unit as occupied
        $unit->update(['status' => 'occupied', 'monthly_rent' => $validated['monthly_rent']]);

        // Generate first month's payment record
        $lease->generateCurrentPeriodPayment();

        // Sync to billing
        $renter = Renter::findOrFail($validated['renter_id']);
        $property = Property::findOrFail($validated['property_id']);
        $billingId = $billing->createLeaseSubscription(
            $renter->name, $renter->email ?? '', $renter->phone,
            $property->name, $unit->unit_number,
            $validated['monthly_rent'], $validated['start_date']
        );

        if ($billingId) {
            $lease->update(['billing_subscription_id' => $billingId]);
        }

        return redirect()->route('leases.show', $lease)->with('success', 'Lease created and billing subscription activated.');
    }

    public function show(Lease $lease)
    {
        $lease->load(['property', 'unit', 'renter', 'rentPayments' => fn($q) => $q->orderByDesc('period')]);
        return view('property.leases.show', compact('lease'));
    }

    public function edit(Lease $lease)
    {
        abort_if($lease->status !== 'pending', 403, 'Only pending leases can be edited in full. Use Terminate to end an active lease.');
        $properties = Property::where('is_active', true)->orderBy('name')->get();
        $renters    = Renter::orderBy('name')->get();
        return view('property.leases.edit', compact('lease', 'properties', 'renters'));
    }

    public function update(Request $request, Lease $lease)
    {
        // Match the same guard as edit() — only pending leases can be fully edited
        abort_if($lease->status !== 'pending', 403, 'Only pending leases can be edited. Use Terminate to end an active lease.');

        $validated = $request->validate([
            'end_date'       => 'nullable|date|after:start_date',
            'monthly_rent'   => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'deposit_paid'   => 'boolean',
            'notes'          => 'nullable|string',
        ]);

        $lease->update($validated);

        // Keep unit.monthly_rent in sync so revenue stats stay accurate
        if (isset($validated['monthly_rent'])) {
            $lease->unit?->update(['monthly_rent' => $validated['monthly_rent']]);
        }

        return redirect()->route('leases.show', $lease)->with('success', 'Lease updated.');
    }

    public function terminate(Request $request, Lease $lease, BillingBridge $billing)
    {
        $request->validate([
            'termination_reason' => 'nullable|string|max:500',
            'terminated_at'      => 'required|date',
        ]);

        abort_if($lease->status === 'terminated', 422, 'Already terminated.');

        $lease->update([
            'status'             => 'terminated',
            'terminated_at'      => $request->terminated_at,
            'termination_reason' => $request->termination_reason,
        ]);

        // Free up the unit
        $lease->unit->update(['status' => 'vacant']);

        // Cancel billing subscription
        if ($lease->billing_subscription_id) {
            $billing->cancelLeaseSubscription($lease->billing_subscription_id);
        }

        return redirect()->route('leases.show', $lease)->with('success', 'Lease terminated. Unit is now vacant.');
    }
}
