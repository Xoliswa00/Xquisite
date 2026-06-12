<?php

namespace App\Http\Controllers;

use App\Models\company;
use App\Http\Requests\StorecompanyRequest;
use App\Http\Requests\UpdatecompanyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;







class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use AuthorizesRequests;






    public function index()
    {
    
            // Normal user sees only their own company
            $company =company::whereHas('user', function ($query) {
                $query->where('platform_owner_id', auth()->id());

            })->firstOrFail();

        
        return view('companies.index', compact('company'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $company = Company::create($validated);

        // Attach creator as owner
        $company->users()->attach(auth::user()->id(), [
            'role' => 'owner'
        ]);

        // Set as current company
        if (auth::user()) {
            auth::user()->update([
                'current_company_id' => $company->id
            ]);
        }

        return response()->json($company, 201);
    }
    public function edit(Company $company)
    {

        return view('companies.edit', compact('company'));
    }



    public function show(Company $company)
    {
        $this->authorize('view', $company);
        return response()->json($company);
    }

    public function update(Request $request, Company $company)
    {
        $this->authorize('update', $company);

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'legal_name' => 'nullable|string|max:255',
        'entity_type' => 'required|string|in:private_company,public_company,partnership,sole_proprietor',
        'slug' => 'required|string|max:255|unique:companies,slug,' . $company->id,

        'registration_number' => 'nullable|string|max:50',
        'tax_number' => 'nullable|string|max:50',
        'vat_number' => 'nullable|string|max:50',

        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:20',
        'website' => 'nullable|url|max:255',
        'billing_email' => 'nullable|email|max:255',

        'address_line_1' => 'nullable|string|max:255',
        'address_line_2' => 'nullable|string|max:255',
        'city' => 'nullable|string|max:100',
        'province' => 'nullable|string|max:100',
        'postal_code' => 'nullable|string|max:20',
        'country' => 'nullable|string|max:100',

        'currency' => 'nullable|string|size:3',
        'default_vat_rate' => 'nullable|numeric|min:0|max:100',
        'vat_registered' => 'nullable|boolean',

        'timezone' => 'nullable|string|max:50',

        'logo' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
    ]);

    // Only platform owner can modify these
    if (Auth::user()->role === 'platform_owner') {
        $validated += $request->validate([
            'domain' => 'nullable|string|max:255',
            'plan' => 'nullable|string|in:basic,premium,enterprise',
            'status' => 'nullable|string|in:active,inactive,suspended',
        ]);
    }

        // Handle logo upload if present
      if ($request->hasFile('logo')) {

    if ($company->logo) {
        Storage::disk('public')->delete($company->logo);
    }

    $path = $request->file('logo')->store('logos', 'public');

    $validated['logo'] = $path;
}

        // Update company
        $company->update($validated);

        return redirect()->route('companies.index')->with('success', 'Company updated successfully');
    }


    public function switch(Company $company)
    {
        $this->authorize('view', $company);

        auth()->user()->update([
            'current_company_id' => $company->id
        ]);

        return response()->json([
            'message' => 'Company switched successfully'
        ]);
    }
}
