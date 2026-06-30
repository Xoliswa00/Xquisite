<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedIp;
use Illuminate\Http\Request;

class BlockedIpController extends Controller
{
    public function index()
    {
        $blocked = BlockedIp::with('blockedBy')
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('admin.security.blocked-ips', compact('blocked'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ip_address'  => 'required|ip',
            'reason'      => 'required|string|max:255',
            'expires_in'  => 'nullable|integer|min:1|max:525600',
        ]);

        BlockedIp::block(
            $request->ip_address,
            $request->reason,
            auth()->id(),
            $request->expires_in
        );

        return back()->with('success', "IP {$request->ip_address} blocked.");
    }

    public function destroy(BlockedIp $blockedIp)
    {
        $ip = $blockedIp->ip_address;
        $blockedIp->unblock();

        return back()->with('success', "IP {$ip} unblocked.");
    }

    public function purgeExpired()
    {
        $count = BlockedIp::where('expires_at', '<', now())->count();
        BlockedIp::where('expires_at', '<', now())->delete();

        return back()->with('success', "Removed {$count} expired block(s).");
    }
}
