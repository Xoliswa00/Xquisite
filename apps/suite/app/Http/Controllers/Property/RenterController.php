<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Modules\Property\Models\Renter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RenterController extends Controller
{
    public function index(Request $request)
    {
        $query = Renter::withCount('leases')->orderBy('name');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $renters = $query->paginate(20)->withQueryString();
        return view('property.renters.index', compact('renters'));
    }

    public function create()
    {
        return view('property.renters.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                    => 'required|string|max:255',
            'email'                   => 'nullable|email|max:255|unique:renters,email',
            'phone'                   => 'nullable|string|max:30',
            'id_number'               => 'nullable|string|max:50',
            'emergency_contact_name'  => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'notes'                   => 'nullable|string',
        ]);

        $renter = Renter::create($validated);

        return redirect()->route('renters.show', $renter)->with('success', 'Renter profile created.');
    }

    public function show(Renter $renter)
    {
        $renter->load(['leases.property', 'leases.unit', 'rentPayments' => fn($q) => $q->latest()->limit(12)]);
        return view('property.renters.show', compact('renter'));
    }

    public function edit(Renter $renter)
    {
        return view('property.renters.edit', compact('renter'));
    }

    public function update(Request $request, Renter $renter)
    {
        $validated = $request->validate([
            'name'                    => 'required|string|max:255',
            'email'                   => 'nullable|email|max:255|unique:renters,email,' . $renter->id,
            'phone'                   => 'nullable|string|max:30',
            'id_number'               => 'nullable|string|max:50',
            'emergency_contact_name'  => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'notes'                   => 'nullable|string',
        ]);

        $renter->update($validated);

        return redirect()->route('renters.show', $renter)->with('success', 'Renter updated.');
    }

    public function destroy(Renter $renter)
    {
        abort_if($renter->leases()->where('status', 'active')->exists(), 422, 'Cannot delete a renter with an active lease.');
        $renter->delete();
        return redirect()->route('renters.index')->with('success', 'Renter removed.');
    }

    /** Send portal invite — creates a password and emails login details */
    public function invite(Renter $renter)
    {
        abort_if(!$renter->email, 422, 'Renter has no email address.');

        $password = \Str::random(10);
        $renter->update(['password' => Hash::make($password)]);

        // TODO: send email with portal URL + temp password
        // Mail::to($renter->email)->send(new RenterPortalInvite($renter, $password));

        return back()->with('success', "Portal access granted. Temporary password: {$password}");
    }
}
