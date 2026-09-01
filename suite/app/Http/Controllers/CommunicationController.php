<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Communication;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\DirectMessageNotification;
use App\Notifications\PlatformMessageNotification;
use App\Services\AuditService;
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

    /**
     * Shared shape for a poll endpoint's JSON response. Body/subject/name are
     * pre-escaped server-side (matching the exact nl2br(e()) the Blade views
     * already use) so the frontend can drop them into innerHTML directly
     * without a second, JS-side escaping mechanism to keep in sync.
     */
    private function formatForPoll($messages, string $ownerLabel, string $otherLabel)
    {
        return $messages->map(fn ($m) => [
            'id'              => $m->id,
            'is_from_owner'   => $m->is_from_owner,
            'subject_html'    => $m->subject ? e($m->subject) : null,
            'body_html'       => nl2br(e($m->body)),
            'created_human'   => $m->created_at->diffForHumans(),
            'from_name_html'  => e($m->is_from_owner ? ($m->fromUser?->name ?? $ownerLabel) : $otherLabel),
        ])->values();
    }

    // ── Tenant-side: staff messages their client ──────────────────────────────

    public function thread(Client $client)
    {
        abort_unless($client->tenant_id === $this->tenantId(), 403);
        abort_unless($client->tenant->hasModule('client_messaging'), 403, 'Client Messaging module not active.');

        $messages = $client->communications()->with('fromUser')->orderBy('created_at')->get();

        // Bulk update — bypasses Communication's Auditable trait, so log the sweep explicitly.
        $markedCount = $client->communications()->where('is_from_owner', false)->whereNull('read_at')->update(['read_at' => now()]);

        if ($markedCount > 0) {
            AuditService::log(
                action: 'Communication.bulk_read',
                entityType: 'Client',
                entityId: $client->id,
                meta: ['count' => $markedCount],
            );
        }

        return view('communications.thread', compact('client', 'messages'));
    }

    /** Polled by the thread view every few seconds — only messages newer than `after_id`. */
    public function pollThread(Request $request, Client $client)
    {
        abort_unless($client->tenant_id === $this->tenantId(), 403);
        abort_unless($client->tenant->hasModule('client_messaging'), 403);

        $afterId  = (int) $request->query('after_id', 0);
        $messages = $client->communications()->with('fromUser')
            ->where('id', '>', $afterId)->orderBy('created_at')->get();

        $markedCount = $client->communications()->where('is_from_owner', false)->whereNull('read_at')->update(['read_at' => now()]);
        if ($markedCount > 0) {
            AuditService::log(action: 'Communication.bulk_read', entityType: 'Client', entityId: $client->id, meta: ['count' => $markedCount]);
        }

        return response()->json([
            'messages' => $this->formatForPoll($messages, 'You', $client->name),
            'last_id'  => $messages->max('id') ?? $afterId,
        ]);
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

    /** Polled by the Platform Support tab — tenant's own view of their thread with Xquisite. */
    public function pollPlatform(Request $request)
    {
        $user   = $this->authedUser();
        $tenant = $user->tenant;

        $afterId  = (int) $request->query('after_id', 0);
        $messages = Communication::whereNull('client_id')->where('tenant_id', $tenant->id)
            ->where('id', '>', $afterId)->with('fromUser')->orderBy('created_at')->get();

        Communication::whereNull('client_id')->where('tenant_id', $tenant->id)
            ->where('is_from_owner', true)->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json([
            'messages' => $this->formatForPoll($messages, 'Xquisite Support', 'You'),
            'last_id'  => $messages->max('id') ?? $afterId,
        ]);
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

    /** Polled by the admin-side thread view — system owner's view of a tenant's thread. */
    public function pollPlatformThread(Request $request, Tenant $tenant)
    {
        $afterId  = (int) $request->query('after_id', 0);
        $messages = Communication::whereNull('client_id')->where('tenant_id', $tenant->id)
            ->where('id', '>', $afterId)->with('fromUser')->orderBy('created_at')->get();

        Communication::whereNull('client_id')->where('tenant_id', $tenant->id)
            ->where('is_from_owner', false)->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json([
            'messages' => $this->formatForPoll($messages, 'Xquisite Support', $tenant->name),
            'last_id'  => $messages->max('id') ?? $afterId,
        ]);
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
