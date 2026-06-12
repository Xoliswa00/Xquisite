<?php

namespace App\Http\Controllers;

use App\Models\client;
use App\Http\Requests\StoreclientRequest;
use App\Http\Requests\UpdateclientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Models\company_invitations;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use App\Notifications\NewClientNotification;






class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */


public function index()
{
    $user = auth()->user();

    // Determine companies the user can access
    if ($user->is_platform_owner) {
        $companyIds = $user->managedCompanies()->pluck('id');
    } else {
        $companyIds = $user->currentCompany
            ? collect([$user->currentCompany->id])
            : collect();
    }

    // Clients list (paginated)
    $clients = Client::whereIn('company_id', $companyIds)
        ->latest()
        ->paginate(10);

    // Dashboard stats
    $stats = [
        'total_clients' => Client::whereIn('company_id', $companyIds)->count(),


        'new_this_month' => Client::whereIn('company_id', $companyIds)
            ->whereMonth('created_at', now()->month)
            ->count(),

        'pending_invites' => company_invitations::whereIn('company_id', $companyIds)
            ->whereNull('created_at')
            ->count(),
    ];

    return view('clients.index', compact(
        'clients',
        'stats'
    ));
}
public function store(Request $request)
{
    $company = auth()->user()->managedCompanies->first();

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'contact_person' => 'nullable|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'nullable|string|max:20',
    ]);

    /*
    |--------------------------------------------------------------------------
    | 1. Create User Account (login identity)
    |--------------------------------------------------------------------------
    */

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
                'password' => Hash::make('ChangeThisPassword'),
        'role' => 'client_user',
    ]);

    /*
    |--------------------------------------------------------------------------
    | 2. Create Client Profile (business data)
    |--------------------------------------------------------------------------
    */

    $client = Client::create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'name' => $validated['name'],
       'contact_person' => $validated['contact_person'] ?? null,
        'email' => $validated['email'],
        'phone' => $validated['phone'] ?? null,
    ]);

    /*
    |--------------------------------------------------------------------------
    | 3. Send Invitation
    |--------------------------------------------------------------------------
    */
    if ($request->send_invitation && $client->email) {

        $token = Str::uuid();

        $invitation = company_invitations::create([
            'company_id' => $company->id,
                'client_id' => $client->id,
            'email' => $client->email,
            'token' => $token,
            'role' => 'client_user',
            'expires_at' => now()->addDays(7),
        ]);

        $link = URL::temporarySignedRoute(
    'client.invitation.accept',
    now()->addDays(7),
    ['token' => $invitation->token]
);

        Mail::to($client->email)->send(
            new \App\Mail\ClientPortalInvite([
                'name' => $client->contact_person ?? $client->name,
                'link' => $link,
                'expires_at' => $invitation->expires_at
            ])
        );
    }

    auth()->user()->notify(new NewClientNotification(
        clientName:  $client->name,
        clientEmail: $client->email,
        clientId:    $client->id,
    ));

    return redirect()
        ->route('clients.index')
        ->with('success', 'Client created successfully');
}

    public function show(Client $client)
    {
        $this->authorize('view', $client);
        return response()->json($client);
    }

      public function update(Request $request)
    {
        $client = auth()->user()->currentCompany->client;
        if (!$client) {
            abort(404);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'billing_address' => 'nullable|string',
            'vat_number' => 'nullable|string',
            'registration_number' => 'nullable|string',
            'contact_person' => 'nullable|string',
        ]);

        $client->update($data);

        return back()->with('success', 'Company profile updated successfully');
   }

    public function create()
{
    return view('clients.create');
}

    public function destroy(Client $client)
    {
       $user = auth()->user();

    // Ensure client belongs to one of the companies the user manages
    $companyIds = $user->managedCompanies()->pluck('id');

    if (! $companyIds->contains($client->company_id)) {
        abort(403, 'Unauthorized action.');
    }

    $client->delete();

  return redirect()
        ->route('clients.index')
        ->with('success', 'Client deleted successfully');
    }
    
    public function accept($token)
{
    $invitation = company_invitations::where('token', $token)
        ->where('expires_at', '>', now())
        ->firstOrFail();




    // Option 1: Automatically log them in if user exists
    $user = $invitation->client->user; // assuming client has a user record
    auth()->login($user);

    // Mark invitation as accepted
    $invitation->update([
        'status' => 'accepted',
        'accepted_at' => now(),
    ]); 

    return redirect()->route('dashboard')->with('success', 'Invitation accepted! Welcome to the client portal.');
}


public function profile()
{

$user = auth()->user();

    if (!$user->isClientUser()) {
        abort(403, 'Unauthorized');
    }

    $client = $user->clients()->first(); // or ->client (better long-term)

    if (!$client) {
        abort(404, 'Client profile not found');
    }

        return view('clients.profile', compact('client'));
    



}




}
