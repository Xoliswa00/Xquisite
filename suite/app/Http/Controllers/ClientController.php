<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Notifications\NewClientNotification;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    private function tenantId(): int
    {
        return auth()->user()->tenant_id ?? abort(403, 'No tenant assigned to this account.');
    }

    private function ownerUser()
    {
        return auth()->user()->tenant->owner();
    }

    public function index()
    {
        $clients = Client::where('tenant_id', $this->tenantId())
            ->latest()
            ->paginate(20);

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:150',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:30',
            'notes' => 'nullable|string|max:1000',
        ]);

        $client = Client::create(array_merge($data, ['tenant_id' => $this->tenantId()]));

        $owner = $this->ownerUser();
        if ($owner) {
            $owner->notify(new NewClientNotification($client));
        }

        return redirect()->route('clients.show', $client)->with('success', 'Client added.');
    }

    public function show(Client $client)
    {
        abort_unless($client->tenant_id === $this->tenantId(), 403);
        $client->load('communications');

        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        abort_unless($client->tenant_id === $this->tenantId(), 403);
        return view('clients.create', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        abort_unless($client->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'name'  => 'required|string|max:150',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:30',
            'notes' => 'nullable|string|max:1000',
        ]);

        $client->update($data);

        return redirect()->route('clients.show', $client)->with('success', 'Client updated.');
    }

    public function destroy(Client $client)
    {
        abort_unless($client->tenant_id === $this->tenantId(), 403);
        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client deleted.');
    }
}
