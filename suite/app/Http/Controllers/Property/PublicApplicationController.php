<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Property\Models\Applicant;
use App\Modules\Property\Models\Property;
use App\Modules\Property\Models\Unit;
use App\Rules\SouthAfricanIdNumber;
use App\Rules\SouthAfricanPhoneNumber;
use App\Services\Tenant\TenantContext;
use Illuminate\Http\Request;

class PublicApplicationController extends Controller
{
    private function resolveTenant(string $slug): Tenant
    {
        $tenant = Tenant::where('slug', $slug)->where('is_active', true)->firstOrFail();
        TenantContext::set($tenant->id);
        return $tenant;
    }

    public function show(string $slug, Property $property, Request $request)
    {
        $tenant = $this->resolveTenant($slug);
        abort_unless($property->is_active, 404);

        $preUnit = $request->filled('unit') ? Unit::where('property_id', $property->id)->find($request->unit) : null;
        $vacantUnits = $preUnit ? collect() : $property->units()->where('status', 'vacant')->orderBy('unit_number')->get();

        return view('property.public.apply', compact('tenant', 'slug', 'property', 'preUnit', 'vacantUnits'));
    }

    public function store(string $slug, Property $property, Request $request)
    {
        $tenant = $this->resolveTenant($slug);
        abort_unless($property->is_active, 404);

        $validated = $request->validate([
            'unit_id'                 => 'nullable|exists:units,id',
            'name'                    => 'required|string|max:255',
            'email'                   => 'nullable|email|max:255',
            'phone'                   => ['nullable', new SouthAfricanPhoneNumber],
            'id_number'               => ['nullable', new SouthAfricanIdNumber],
            'employer'                => 'nullable|string|max:255',
            'employment_type'         => 'nullable|in:permanent,contract,self_employed,unemployed,other',
            'employment_months'       => 'nullable|integer|min:0',
            'monthly_income'          => 'nullable|numeric|min:0',
            'monthly_expenses'        => 'nullable|numeric|min:0',
            'number_of_occupants'     => 'nullable|integer|min:0|max:255',
            'previous_landlord_name'  => 'nullable|string|max:255',
            'previous_landlord_phone' => ['nullable', new SouthAfricanPhoneNumber],
            'notes'                   => 'nullable|string',
            'documents.id_copy.*'             => 'file|mimes:jpg,jpeg,png,heic,heif,webp,pdf|max:15360',
            'documents.proof_of_income.*'     => 'file|mimes:jpg,jpeg,png,heic,heif,webp,pdf|max:15360',
            'documents.bank_statement.*'      => 'file|mimes:jpg,jpeg,png,heic,heif,webp,pdf|max:15360',
            'documents.proof_of_residence.*'  => 'file|mimes:jpg,jpeg,png,heic,heif,webp,pdf|max:15360',
        ], [
            'documents.*.*.mimes' => 'That file type isn\'t supported — please upload a JPG, PNG, HEIC or PDF.',
            'documents.*.*.max'   => 'That file is too large — please keep each file under 15MB.',
        ]);

        // A unit belonging to another property must not silently attach — same
        // foreign-tenant/foreign-property guard used elsewhere for exists-only checks.
        if (!empty($validated['unit_id'])) {
            $validated['unit_id'] = Unit::where('property_id', $property->id)->find($validated['unit_id'])?->id;
        }

        $applicant = Applicant::create([
            'tenant_id'               => $tenant->id,
            'property_id'             => $property->id,
            'unit_id'                 => $validated['unit_id'] ?? null,
            'name'                    => $validated['name'],
            'email'                   => $validated['email'] ?? null,
            'phone'                   => $validated['phone'] ?? null,
            'id_number'               => $validated['id_number'] ?? null,
            'employer'                => $validated['employer'] ?? null,
            'employment_type'         => $validated['employment_type'] ?? null,
            'employment_months'       => $validated['employment_months'] ?? null,
            'monthly_income'          => $validated['monthly_income'] ?? null,
            'monthly_expenses'        => $validated['monthly_expenses'] ?? null,
            'number_of_occupants'     => $validated['number_of_occupants'] ?? null,
            'previous_landlord_name'  => $validated['previous_landlord_name'] ?? null,
            'previous_landlord_phone' => $validated['previous_landlord_phone'] ?? null,
            'notes'                   => $validated['notes'] ?? null,
            'status'                  => 'pending',
        ]);

        foreach (['id_copy', 'proof_of_income', 'bank_statement', 'proof_of_residence'] as $type) {
            foreach ($request->file("documents.{$type}", []) as $file) {
                $applicant->documents()->create([
                    'tenant_id'     => $tenant->id,
                    'type'          => $type,
                    'path'          => $file->store('applicant-documents', 'public'),
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }
        }

        return redirect()->route('apply.thanks', [$slug, $property]);
    }

    public function thanks(string $slug, Property $property)
    {
        $tenant = $this->resolveTenant($slug);

        return view('property.public.apply-thanks', compact('tenant', 'property'));
    }
}
