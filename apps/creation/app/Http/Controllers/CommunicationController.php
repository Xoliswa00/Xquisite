<?php

namespace App\Http\Controllers;

use App\Models\client;
use App\Models\Communication;
use App\Notifications\DirectMessageNotification;
use Illuminate\Http\Request;

class CommunicationController extends Controller
{
    // ─── OWNER SIDE ──────────────────────────────────────────────

    /** Owner views the message thread with a specific client */
    public function thread(client $client)
    {
        $companyId = auth()->user()->managedCompanies->first()->id;
        abort_if($client->company_id !== $companyId, 403);

        $messages = Communication::where('client_id', $client->id)
            ->with('fromUser')
            ->oldest()
            ->get();

        // Mark messages from the client as read
        Communication::where('client_id', $client->id)
            ->where('is_from_owner', false)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('communications.thread', compact('client', 'messages'));
    }

    /** Owner sends a message to a client */
    public function store(Request $request, client $client)
    {
        $companyId = auth()->user()->managedCompanies->first()->id;
        abort_if($client->company_id !== $companyId, 403);

        $validated = $request->validate([
            'subject' => 'nullable|string|max:255',
            'body'    => 'required|string|max:5000',
        ]);

        Communication::create([
            'company_id'    => $companyId,
            'client_id'     => $client->id,
            'from_user_id'  => auth()->id(),
            'to_user_id'    => $client->user_id,
            'subject'       => $validated['subject'] ?? null,
            'body'          => $validated['body'],
            'is_from_owner' => true,
        ]);

        // Notify the client user
        if ($client->user) {
            $client->user->notify(new DirectMessageNotification(
                fromName:  auth()->user()->name,
                subject:   $validated['subject'] ?? 'New message',
                preview:   \Str::limit($validated['body'], 120),
                fromOwner: true,
                clientId:  $client->id,
            ));
        }

        return back()->with('success', 'Message sent.');
    }

    // ─── CLIENT SIDE ─────────────────────────────────────────────

    /** Client views all their messages */
    public function clientIndex()
    {
        $user   = auth()->user();
        $client = client::where('user_id', $user->id)->firstOrFail();

        $messages = Communication::where('client_id', $client->id)
            ->with('fromUser')
            ->oldest()
            ->get();

        // Mark owner messages as read
        Communication::where('client_id', $client->id)
            ->where('is_from_owner', true)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('communications.client', compact('messages', 'client'));
    }

    /** Client replies to the owner */
    public function clientReply(Request $request)
    {
        $user   = auth()->user();
        $client = client::where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        // Find owner user (platform_owner of the company)
        $owner = $client->company->user;

        Communication::create([
            'company_id'    => $client->company_id,
            'client_id'     => $client->id,
            'from_user_id'  => $user->id,
            'to_user_id'    => $owner->id,
            'subject'       => null,
            'body'          => $validated['body'],
            'is_from_owner' => false,
        ]);

        if ($owner) {
            $owner->notify(new DirectMessageNotification(
                fromName:  $user->name,
                subject:   'Reply from client',
                preview:   \Str::limit($validated['body'], 120),
                fromOwner: false,
                clientId:  $client->id,
            ));
        }

        return back()->with('success', 'Reply sent.');
    }
}
