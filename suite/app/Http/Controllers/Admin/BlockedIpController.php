<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedIp;
use App\Support\IpLocation;
use Illuminate\Http\Request;

class BlockedIpController extends Controller
{
    public function index()
    {
        $blocked = BlockedIp::with('blockedBy')
            ->orderByDesc('created_at')
            ->paginate(50);

        // Only the active page's rows are geocoded — this hits the external
        // ip-api.com lookup, so keep it bounded to what's actually rendered.
        $points = $blocked->getCollection()
            ->map(function ($entry) {
                $geo = IpLocation::geocode($entry->ip_address);

                if (!$geo || !$geo['lat'] || !$geo['lon']) {
                    return null;
                }

                return [
                    'ip'      => $entry->ip_address,
                    'reason'  => $entry->reason,
                    'lat'     => $geo['lat'],
                    'lon'     => $geo['lon'],
                    'label'   => implode(', ', array_filter([$geo['city'], $geo['country']])),
                    'expired' => $entry->isExpired(),
                ];
            })
            ->filter()
            ->values();

        return view('admin.security.blocked-ips', compact('blocked', 'points'));
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
        $expired = BlockedIp::where('expires_at', '<', now())->get();
        $expired->each->unblock();

        return back()->with('success', "Removed {$expired->count()} expired block(s).");
    }
}
