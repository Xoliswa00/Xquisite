<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedIp;
use App\Services\AuditService;
use App\Services\Security\LoginThrottleService;
use App\Support\IpLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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

        $pauses = $this->recentPauses();

        return view('admin.security.blocked-ips', compact('blocked', 'points', 'pauses'));
    }

    /**
     * Automatic sign-in pauses from the last day. They live in the cache, so the
     * audit log is the only list of them; each row is checked against the cache
     * to see whether it is still running.
     */
    private function recentPauses()
    {
        $seen = [];

        return DB::table('audit_logs')
            ->where('action', 'auth.sign_in_paused')
            ->where('created_at', '>=', now()->subDay())
            // Newest first by time, so the (action, created_at) index serves both
            // the filter and the order.
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(50)
            ->get(['ip_address', 'meta', 'created_at'])
            ->map(function ($row) use (&$seen) {
                $meta = json_decode($row->meta ?? '[]', true) ?: [];

                $pause = [
                    'at'      => Carbon::parse($row->created_at),
                    'ip'      => (string) $row->ip_address,
                    'scope'   => $meta['scope'] ?? 'account',
                    'guard'   => $meta['guard'] ?? null,
                    'channel' => $meta['channel'] ?? null,
                    'email'   => $meta['email'] ?? null,
                    'tenant'  => $meta['tenant_slug'] ?? null,
                ];

                // Rows come newest first. The cache only knows whether a pause is
                // running now, so an older row for the same pause must read "Ended".
                $key = implode('|', [$pause['scope'], $pause['ip'], $pause['guard'], $pause['channel'], $pause['email']]);
                $isLatest = ! isset($seen[$key]);
                $seen[$key] = true;

                $pause['seconds_left'] = $isLatest
                    ? LoginThrottleService::pauseSecondsLeft($pause['scope'], $pause['ip'], $pause['guard'], $pause['channel'], $pause['email'])
                    : 0;

                return $pause;
            });
    }

    public function liftPause(Request $request)
    {
        $data = $request->validate([
            'scope'   => 'required|in:account,ip',
            'ip'      => 'required|ip',
            'guard'   => 'nullable|required_if:scope,account|in:' . implode(',', LoginThrottleService::GUARDS),
            'channel' => 'nullable|required_if:scope,account|in:' . implode(',', LoginThrottleService::CHANNELS),
            'email'   => 'nullable|required_if:scope,account|email|max:190',
        ]);

        LoginThrottleService::unlock($data['scope'], $data['ip'], $data['guard'] ?? null, $data['channel'] ?? null, $data['email'] ?? null);

        AuditService::log(
            action: 'auth.sign_in_pause_lifted',
            meta: array_filter($data),
        );

        return back()->with('success', $data['scope'] === 'ip'
            ? "Sign-in pause lifted for {$data['ip']}."
            : "Sign-in pause lifted for {$data['email']}.");
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
