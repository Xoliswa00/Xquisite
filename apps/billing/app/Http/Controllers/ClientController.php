<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\CompanyInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;

class ClientController extends Controller
{
    public function index()
    {
        $company = auth()->user()->currentCompany;

        $clients = $company
            ? Client::where('company_id', $company->id)->latest()->paginate(15)
            : collect();

        $stats = $company ? [
            'total'          => Client::where('company_id', $company->id)->count(),
            'new_this_month' => Client::where('company_id', $company->id)->whereMonth('created_at', now()->month)->count(),
            'pending_invites' => CompanyInvitation::where('company_id', $company->id)->whereNull('accepted_at')->count(),
        ] : ['total' => 0, 'new_this_month' => 0, 'pending_invites' => 0];

        return view('clients.index', compact('clients', 'stats'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $company = auth()->user()->currentCompany;
        abort_if(!$company, 403, 'No active company.');

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email'          => 'required|email|unique:clients,email',
            'phone'          => 'nullable|string|max:20',
            'vat_number'     => 'nullable|string|max:50',
            'billing_address' => 'nullable|string',
        ]);

        $client = Client::create([
            'company_id'      => $company->id,
            'name'            => $validated['name'],
            'contact_person'  => $validated['contact_person'] ?? null,
            'email'           => $validated['email'],
            'phone'           => $validated['phone'] ?? null,
            'vat_number'      => $validated['vat_number'] ?? null,
            'billing_address' => $validated['billing_address'] ?? null,
        ]);

        if ($request->boolean('send_invitation') && $client->email) {
            $token = Str::uuid();

            $invitation = CompanyInvitation::create([
                'company_id' => $company->id,
                'client_id'  => $client->id,
                'email'      => $client->email,
                'token'      => $token,
                'role'       => 'client_user',
                'expires_at' => now()->addDays(7),
            ]);

            $link = URL::temporarySignedRoute(
                'client.invitation.accept',
                now()->addDays(7),
                ['token' => $invitation->token]
            );

            Mail::to($client->email)->send(
                new \App\Mail\ClientPortalInvite([
                    'name'       => $client->contact_person ?? $client->name,
                    'link'       => $link,
                    'expires_at' => $invitation->expires_at,
                ])
            );
        }

        return redirect()->route('clients.index')->with('success', 'Client created successfully.');
    }

    public function show(Client $client)
    {
        $this->authorize('view', $client);
        $client->load(['invoices', 'quotes']);
        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        $this->authorize('update', $client);
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'contact_person'  => 'nullable|string|max:255',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'nullable|string|max:20',
            'vat_number'      => 'nullable|string|max:50',
            'billing_address' => 'nullable|string',
        ]);

        $client->update($validated);

        return redirect()->route('clients.show', $client)->with('success', 'Client updated.');
    }

    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Client deleted.');
    }

    public function profile()
    {
        $user = auth()->user();
        $client = Client::where('email', $user->email)->first();

        if (!$client) {
            abort(404, 'Client profile not found.');
        }

        return view('clients.profile', compact('client'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $client = Client::where('email', $user->email)->firstOrFail();

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'contact_person'  => 'nullable|string',
            'phone'           => 'nullable|string',
            'billing_address' => 'nullable|string',
            'vat_number'      => 'nullable|string',
        ]);

        $client->update($validated);

        return back()->with('success', 'Profile updated.');
    }

    public function accept($token)
    {
        $invitation = CompanyInvitation::where('token', $token)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $user = $invitation->client?->user ?? User::where('email', $invitation->email)->first();

        if ($user) {
            auth()->login($user);
        }

        $invitation->update([
            'status'      => 'accepted',
            'accepted_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Invitation accepted! Welcome.');
    }
}
