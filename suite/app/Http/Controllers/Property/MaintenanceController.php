<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Modules\Property\Models\Contractor;
use App\Modules\Property\Models\MaintenanceRequest;
use App\Modules\Property\Models\Property;
use App\Modules\Property\Models\Unit;
use App\Modules\Property\Models\Lease;
use App\Services\AuditService;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = MaintenanceRequest::with(['property', 'unit', 'renter'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        $requests   = $query->paginate(20)->withQueryString();
        $properties = Property::where('is_active', true)->orderBy('name')->get();

        return view('property.maintenance.index', compact('requests', 'properties'));
    }

    public function create(Request $request)
    {
        $properties = Property::where('is_active', true)->orderBy('name')->get();
        $preUnit    = $request->filled('unit_id') ? Unit::find($request->unit_id) : null;
        return view('property.maintenance.create', compact('properties', 'preUnit'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'unit_id'     => 'required|exists:units,id',
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'priority'    => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|string|max:255',
        ]);

        // Try to link to current active lease/renter
        $unit  = Unit::find($validated['unit_id']);
        $lease = $unit?->activeLease;

        if ($lease) {
            $validated['lease_id']  = $lease->id;
            $validated['renter_id'] = $lease->renter_id;
        }

        MaintenanceRequest::create($validated);

        return redirect()->route('maintenance.index')->with('success', 'Maintenance request created.');
    }

    public function show(MaintenanceRequest $maintenance)
    {
        $maintenance->load(['property', 'unit', 'renter', 'lease', 'photos', 'contractor', 'invitedContractors', 'quotes.contractor']);
        $contractors = Contractor::where('is_active', true)->orderBy('name')->get();
        return view('property.maintenance.show', compact('maintenance', 'contractors'));
    }

    /**
     * Invite one or more contractors to quote on this job. Whichever quote gets approved becomes the assigned contractor.
     *
     * ->sync() writes directly to the pivot table and fires no Eloquent events, so there's
     * no model to hang Auditable off — logged explicitly instead.
     */
    public function assignContractors(Request $request, MaintenanceRequest $maintenance)
    {
        $validated = $request->validate([
            'contractor_ids'   => 'nullable|array',
            'contractor_ids.*' => 'exists:contractors,id',
        ]);

        $changes = $maintenance->invitedContractors()->sync($validated['contractor_ids'] ?? []);

        if ($changes['attached'] || $changes['detached']) {
            AuditService::log(
                action: 'maintenance_request.contractors_invited',
                entityType: 'MaintenanceRequest',
                entityId: $maintenance->id,
                meta: ['attached' => $changes['attached'], 'detached' => $changes['detached']],
            );
        }

        $count = count($validated['contractor_ids'] ?? []);
        return redirect()->route('maintenance.show', $maintenance)
            ->with('success', $count > 0 ? "Invited {$count} contractor(s) to quote." : 'No contractors invited.');
    }

    public function edit(MaintenanceRequest $maintenance)
    {
        $properties = Property::where('is_active', true)->orderBy('name')->get();
        return view('property.maintenance.edit', compact('maintenance', 'properties'));
    }

    public function update(Request $request, MaintenanceRequest $maintenance)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'required|string',
            'priority'         => 'required|in:low,medium,high,urgent',
            'assigned_to'      => 'nullable|string|max:255',
            'resolution_notes' => 'nullable|string',
        ]);

        $maintenance->update($validated);
        return redirect()->route('maintenance.show', $maintenance)->with('success', 'Request updated.');
    }

    public function updateStatus(Request $request, MaintenanceRequest $maintenance)
    {
        $validated = $request->validate([
            'status'           => 'required|in:open,in_progress,resolved,closed',
            'resolution_notes' => 'nullable|string',
            'assigned_to'      => 'nullable|string|max:255',
        ]);

        if ($validated['status'] === 'resolved' && !$maintenance->resolved_at) {
            $validated['resolved_at'] = now();
        }

        $maintenance->update($validated);

        return redirect()->route('maintenance.show', $maintenance)->with('success', 'Status updated.');
    }

    public function destroy(MaintenanceRequest $maintenance)
    {
        $maintenance->delete();
        return redirect()->route('maintenance.index')->with('success', 'Request deleted.');
    }
}
