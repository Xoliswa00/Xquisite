<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VerifiedIp;
use App\Services\Security\IpReputationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class IpReputationController extends Controller
{
    public function index()
    {
        $ips = IpReputationService::ipsWithSharedAccounts();
        $verifiedRecords = VerifiedIp::pluck('id', 'ip_address');

        return view('admin.security.ip-reputation', compact('ips', 'verifiedRecords'));
    }

    public function verify(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'note'       => 'nullable|string|max:500',
        ]);

        VerifiedIp::updateOrCreate(
            ['ip_address' => $request->ip_address],
            [
                'note'        => $request->note,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]
        );
        Cache::forget('ip_reputation:flagged_count');

        return back()->with('success', "IP {$request->ip_address} marked as verified.");
    }

    public function unverify(VerifiedIp $verifiedIp)
    {
        $ip = $verifiedIp->ip_address;
        $verifiedIp->delete();
        Cache::forget('ip_reputation:flagged_count');

        return back()->with('success', "IP {$ip} unmarked — it will be flagged again if it re-qualifies.");
    }
}
