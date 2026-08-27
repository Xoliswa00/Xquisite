<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Modules\Property\Models\Applicant;
use App\Modules\Property\Models\Property;
use App\Modules\Property\Models\Renter;
use App\Modules\Property\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApplicantController extends Controller
{
    public function index(Request $request)
    {
        $query = Applicant::with(['property', 'unit'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $applicants = $query->paginate(20)->withQueryString();

        return view('property.applicants.index', compact('applicants'));
    }

    public function create(Request $request)
    {
        $properties = Property::where('is_active', true)->orderBy('name')->get();
        $preUnit    = $request->filled('unit_id') ? Unit::find($request->unit_id) : null;

        return view('property.applicants.create', compact('properties', 'preUnit'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id'    => 'nullable|exists:properties,id',
            'unit_id'        => 'nullable|exists:units,id',
            'name'           => 'required|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:30',
            'id_number'      => 'nullable|string|max:50',
            'employer'       => 'nullable|string|max:255',
            'employment_type'   => 'nullable|in:permanent,contract,self_employed,unemployed,other',
            'employment_months' => 'nullable|integer|min:0',
            'monthly_income'     => 'nullable|numeric|min:0',
            'monthly_expenses'   => 'nullable|numeric|min:0',
            'number_of_occupants'      => 'nullable|integer|min:0|max:255',
            'previous_landlord_name'   => 'nullable|string|max:255',
            'previous_landlord_phone'  => 'nullable|string|max:30',
            'notes'          => 'nullable|string',
        ]);

        $applicant = Applicant::create($this->scopeToOwnTenant($validated));

        return redirect()->route('applicants.show', $applicant)->with('success', 'Applicant added.');
    }

    public function show(Applicant $applicant)
    {
        $applicant->load(['property', 'unit', 'renter', 'documents']);
        return view('property.applicants.show', compact('applicant'));
    }

    public function edit(Applicant $applicant)
    {
        abort_if($applicant->status === 'converted', 403, 'This applicant has already been converted to a renter.');
        $properties = Property::where('is_active', true)->orderBy('name')->get();
        return view('property.applicants.edit', compact('applicant', 'properties'));
    }

    public function update(Request $request, Applicant $applicant)
    {
        abort_if($applicant->status === 'converted', 403, 'This applicant has already been converted to a renter.');

        $validated = $request->validate([
            'property_id'    => 'nullable|exists:properties,id',
            'unit_id'        => 'nullable|exists:units,id',
            'name'           => 'required|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:30',
            'id_number'      => 'nullable|string|max:50',
            'employer'       => 'nullable|string|max:255',
            'employment_type'   => 'nullable|in:permanent,contract,self_employed,unemployed,other',
            'employment_months' => 'nullable|integer|min:0',
            'monthly_income'     => 'nullable|numeric|min:0',
            'monthly_expenses'   => 'nullable|numeric|min:0',
            'number_of_occupants'      => 'nullable|integer|min:0|max:255',
            'previous_landlord_name'   => 'nullable|string|max:255',
            'previous_landlord_phone'  => 'nullable|string|max:30',
            'notes'          => 'nullable|string',
        ]);

        $applicant->update($this->scopeToOwnTenant($validated));

        return redirect()->route('applicants.show', $applicant)->with('success', 'Applicant updated.');
    }

    /**
     * `exists:properties,id` / `exists:units,id` check existence only — they don't
     * respect the HasTenant global scope, so a submitted ID belonging to another
     * tenant would otherwise pass validation. Re-resolving through the tenant-scoped
     * model drops it back to null instead of silently attaching a foreign-tenant ID.
     */
    private function scopeToOwnTenant(array $validated): array
    {
        if (!empty($validated['property_id'])) {
            $validated['property_id'] = Property::find($validated['property_id'])?->id;
        }

        if (!empty($validated['unit_id'])) {
            $validated['unit_id'] = Unit::find($validated['unit_id'])?->id;
        }

        return $validated;
    }

    /** Record the screening decision — approve or reject. */
    public function screen(Request $request, Applicant $applicant)
    {
        abort_if($applicant->status === 'converted', 403, 'This applicant has already been converted to a renter.');

        $validated = $request->validate([
            'decision'         => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:decision,rejected|nullable|string|max:255',
        ]);

        $applicant->update([
            'status'           => $validated['decision'],
            'screened_at'      => now(),
            'screened_by'      => auth()->id(),
            'rejection_reason' => $validated['decision'] === 'rejected' ? $validated['rejection_reason'] : null,
        ]);

        return back()->with('success', 'Applicant ' . $validated['decision'] . '.');
    }

    /** Turn an approved applicant into an actual Renter record — kept as a separate, explicit step. */
    public function convert(Applicant $applicant)
    {
        // Idempotent: a double-click, a resubmit, or landing back here via the
        // browser's back button after a successful convert should land the user
        // on the renter that already exists, not a dead-end validation error.
        if ($applicant->status === 'converted') {
            return redirect()->route('renters.show', $applicant->renter)
                ->with('success', 'Applicant already converted to renter.');
        }

        $renter = DB::transaction(function () use ($applicant) {
            // Lock so two concurrent clicks can't both pass the status check
            // above and each create their own Renter row.
            $applicant = Applicant::whereKey($applicant->id)->lockForUpdate()->firstOrFail();

            abort_unless($applicant->status === 'approved', 422, 'Only approved applicants can be converted to a renter.');

            $renter = Renter::create([
                'applicant_id' => $applicant->id,
                'name'         => $applicant->name,
                'email'        => $applicant->email,
                'phone'        => $applicant->phone,
                'id_number'    => $applicant->id_number,
                'notes'        => $applicant->notes,
            ]);

            $applicant->update(['status' => 'converted']);

            return $renter;
        });

        return redirect()->route('renters.show', $renter)->with('success', 'Applicant converted to renter. You can now create their lease.');
    }
}
