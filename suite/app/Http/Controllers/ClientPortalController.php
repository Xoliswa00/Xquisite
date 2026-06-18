<?php

namespace App\Http\Controllers;

use App\Models\Communication;
use App\Models\User;

class ClientPortalController extends Controller
{
    public function dashboard()
    {
        /** @var User $user */
        $user   = auth()->user();
        $tenant = $user->tenant;

        $platformUnread = Communication::whereNull('client_id')
            ->where('tenant_id', $tenant->id)
            ->where('is_from_owner', true)
            ->whereNull('read_at')
            ->count();

        $hasClientMessaging = $tenant->hasModule('client_messaging');

        $clientUnread = $hasClientMessaging
            ? Communication::whereNotNull('client_id')
                ->where('tenant_id', $tenant->id)
                ->where('is_from_owner', false)
                ->whereNull('read_at')
                ->count()
            : 0;

        $unreadNotifCount    = $user->unreadNotifications()->count();
        $recentNotifications = $user->notifications()->latest()->take(6)->get();

        return view('portal.dashboard', compact(
            'tenant', 'platformUnread', 'clientUnread',
            'hasClientMessaging', 'unreadNotifCount', 'recentNotifications'
        ));
    }
}
