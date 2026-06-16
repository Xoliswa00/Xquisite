<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Communication;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\DirectMessageNotification;
use App\Notifications\PlatformMessageNotification;
use Illuminate\Http\Request;

class CommunicationController extends Controller
{
    private function authedUser(): User
    {
        /** @var User */
        return auth()->user();
    }

    private function tenantId(): int
    {
        return $this->authedUser()->tenant_id ?? abort(403, 'No tenant assigned to this account.');
    }

    // ── Tenant-side: staff messages their client ──────────────────────────────

    public function thread(Client $client)
    {
        abort_unless($client->tenant_id === $this->tenantId(), 403);

        $messages = $client->communications()->with('fromUser')->orderBy('created_at')->get();

        $client->communications()->where('is_from_owner', false)->whereNull('read_at')->update(['read_at' => now()]);

        return view('communications.thread', compact('client', 'messages'));
    }

    public function store(Request $request, Client $client)
    {
        $user = $this->authedUser();
        abort_unless($client->tenant_id === $user->tenant_id, 403);
        abort_unless($user->tenant->hasModule('client_messaging'), 403, 'Client Messaging module not active.');

        $data = $request->validate([
            'subject' => 'nullable|string|max:150',
            'body'    => 'required|string|max:5000',
        ]);

        Communication::create([
            'tenant_id'     => $user->tenant_id,
            'client_id'     => $client->id,
            'from_user_id'  => $user->id,
            'subject'       => $data['subject'] ?? null,
            'body'          => $data['body'],
            'is_from_owner' => true,
        ]);

        if ($client->user_id && $client->user) {
            $client->user->notify(new DirectMessageNotification($client, substr($data['body'], 0, 100), true));
        }

        return back()->with('success', 'Message sent.');
    }

    // ── Portal (tenant view): both channels ───────────────────────────────────

    public function clientIndex()
    {
        $user   = $this->authedUser();
        $tenant = $user->tenant;

        // Platform channel — messages from/to system owner (client_id IS NULL)
        $platformMessages = Communication::whereNull('client_id')
            ->where('tenant_id', $tenant->id)
            ->with('fromUser')
            ->orderBy('created_at')
            ->get();

        // Mark platform messages from owner as read
        Communication::whereNull('client_id')
            ->where('tenant_id', $tenant->id)
            ->where('is_from_owner', true)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Client messaging channel — only if module is active
        $hasClientMessaging = $tenant->hasModule('client_messaging');
        $clientThreads      = collect();

        if ($hasClientMessaging) {
            $clientThreads = Client::where('tenant_id', $tenant->id)
                ->with(['communications' => fn($q) => $q->latest()->limit(1)])
                ->withCount(['communications as unread_count' => fn($q) => $q
                    ->where('is_from_owner', false)
                    ->whereNull('read_at'),
                ])
                ->get();
        }

        return view('communications.client', compact('platformMessages', 'hasClientMessaging', 'clientThreads'));
    }

    public function clientReply(Request $request)
    {
        $user   = $this->authedUser();
        $tenant = $user->tenant;

        $data = $request->validate([
            'body'      => 'required|string|max:5000',
            'client_id' => 'nullable|exists:clients,id',
        ]);

        if (!empty($data['client_id'])) {
            // Tenant replying to one of their clients (paid module)
            abort_unless($tenant->hasModule('client_messaging'), 403);
            $client = Client::where('id', $data['client_id'])->where('tenant_id', $tenant->id)->firstOrFail();

            Communication::create([
                'tenant_id'     => $tenant->id,
                'client_id'     => $client->id,
                'from_user_id'  => $user->id,
                'body'          => $data['body'],
                'is_from_owner' => true,
            ]);

            if ($client->user) {
                $client->user->notify(new DirectMessageNotification($client, substr($data['body'], 0, 100), true));
            }
        } else {
            // Tenant replying to platform/system owner
            Communication::create([
                'tenant_id'     => $tenant->id,
                'client_id'     => null,
                'from_user_id'  => $user->id,
                'body'          => $data['body'],
                'is_from_owner' => false,
            ]);

            // Notify platform admins
            User::where('tenant_id', null)
                ->whereHas('roles', fn($q) => $q->where('name', 'super-admin'))
                ->get()
                ->each(fn($admin) => $admin->notify(
                    new PlatformMessageNotification($tenant, substr($data['body'], 0, 100), false)
                ));
        }

        return back()->with('success', 'Message sent.');
    }

    // ── Admin side: system owner ↔ tenant ─────────────────────────────────────

    public function platformThread(Tenant $tenant)
    {
        $messages = Communication::whereNull('client_id')
            ->where('tenant_id', $tenant->id)
            ->with('fromUser')
            ->orderBy('created_at')
            ->get();

        // Mark tenant replies as read
        Communication::whereNull('client_id')
            ->where('tenant_id', $tenant->id)
            ->where('is_from_owner', false)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('admin.tenant-messages.thread', compact('tenant', 'messages'));
    }

    public function platformStore(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'subject' => 'nullable|string|max:150',
            'body'    => 'required|string|max:5000',
        ]);

        Communication::create([
            'tenant_id'     => $tenant->id,
            'client_id'     => null,
            'from_user_id'  => $this->authedUser()->id,
            'subject'       => $data['subject'] ?? null,
            'body'          => $data['body'],
            'is_from_owner' => true,
        ]);

        // Notify all users of this tenant
        $tenant->users()->get()->each(
            fn($u) => $u->notify(new PlatformMessageNotification($tenant, substr($data['body'], 0, 100), true))
        );

        return back()->with('success', 'Message sent to tenant.');
    }
}
