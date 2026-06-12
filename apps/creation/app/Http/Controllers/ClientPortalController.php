<?php

namespace App\Http\Controllers;

use App\Models\client;
use App\Models\Communication;
use Illuminate\Http\Request;

class ClientPortalController extends Controller
{
    public function dashboard()
    {
        $user   = auth()->user();
        $client = client::where('user_id', $user->id)
            ->with(['quotes', 'invoices', 'payments'])
            ->firstOrFail();

        $unreadMessages = Communication::where('client_id', $client->id)
            ->where('is_from_owner', true)
            ->whereNull('read_at')
            ->count();

        $recentNotifications = $user->notifications()->latest()->take(5)->get();
        $unreadNotifCount    = $user->unreadNotifications()->count();

        return view('portal.dashboard', compact(
            'client',
            'unreadMessages',
            'recentNotifications',
            'unreadNotifCount',
        ));
    }
}
