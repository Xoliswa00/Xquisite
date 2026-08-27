<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Mail\ContractorPortalInvite;
use App\Modules\Property\Models\Contractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ContractorController extends Controller
{
    public function index(Request $request)
    {
        $query = Contractor::withCount('maintenanceRequests')->orderBy('name');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('company_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $contractors = $query->paginate(20)->withQueryString();
        return view('property.contractors.index', compact('contractors'));
    }

    public function create()
    {
        return view('property.contractors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'trade'        => 'nullable|string|max:255',
            'email'        => 'nullable|email|max:255|unique:contractors,email',
            'phone'        => 'nullable|string|max:30',
            'notes'        => 'nullable|string',
        ]);

        $contractor = Contractor::create($validated);

        return redirect()->route('contractors.show', $contractor)->with('success', 'Contractor added.');
    }

    public function show(Contractor $contractor)
    {
        $contractor->load([
            'maintenanceRequests' => fn ($q) => $q->latest(),
            'invitedJobs' => fn ($q) => $q->latest(),
            'quotes' => fn ($q) => $q->latest(),
        ]);
        return view('property.contractors.show', compact('contractor'));
    }

    public function edit(Contractor $contractor)
    {
        return view('property.contractors.edit', compact('contractor'));
    }

    public function update(Request $request, Contractor $contractor)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'trade'        => 'nullable|string|max:255',
            'email'        => 'nullable|email|max:255|unique:contractors,email,' . $contractor->id,
            'phone'        => 'nullable|string|max:30',
            'is_active'    => 'boolean',
            'notes'        => 'nullable|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $contractor->update($validated);

        return redirect()->route('contractors.show', $contractor)->with('success', 'Contractor updated.');
    }

    public function destroy(Contractor $contractor)
    {
        abort_if(
            $contractor->maintenanceRequests()->whereNotIn('status', ['resolved', 'closed'])->exists(),
            422,
            'Cannot remove a contractor with open jobs assigned.'
        );
        $contractor->delete();
        return redirect()->route('contractors.index')->with('success', 'Contractor removed.');
    }

    /** Send portal invite — creates a password and emails login details */
    public function invite(Contractor $contractor)
    {
        abort_if(! $contractor->email, 422, 'Contractor has no email address.');

        $password = \Illuminate\Support\Str::random(12);
        $contractor->update(['password' => Hash::make($password)]);

        Mail::to($contractor->email)->queue(new ContractorPortalInvite($contractor, $password));

        return back()->with('success', "Portal invite sent to {$contractor->email}. The contractor will receive their login details by email.");
    }
}
