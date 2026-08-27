<?php

namespace App\Services\Security;

use App\Models\Tenant;
use App\Models\User;
use App\Models\VerifiedIp;
use App\Modules\Booking\Models\Customer;
use App\Modules\Property\Models\Renter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Surfaces IPs that have logged into more than one distinct account, using the
 * audit_logs trail every guard's successful login already writes. Same-tenant
 * sharing (an office/shop on one WiFi) is normal and shown but not flagged —
 * only accounts spanning more than CROSS_TENANT_THRESHOLD distinct tenants on
 * one IP is treated as "strange" until an admin marks that IP verified.
 */
class IpReputationService
{
    private const SUCCESS_ACTIONS = ['auth.login', 'customer.login', 'renter.login'];
    private const LOOKBACK_DAYS = 90;
    private const CROSS_TENANT_THRESHOLD = 3; // more than this many distinct tenants = flagged

    /**
     * @return Collection<int, array{
     *   ip_address: string, total_accounts: int, distinct_tenants: int,
     *   same_tenant_max: int, is_flagged: bool, is_verified: bool,
     *   last_seen: string, accounts: array
     * }>
     */
    public static function ipsWithSharedAccounts(): Collection
    {
        $rows = DB::table('audit_logs')
            ->whereIn('action', self::SUCCESS_ACTIONS)
            ->whereNotNull('ip_address')
            ->where('created_at', '>=', now()->subDays(self::LOOKBACK_DAYS))
            ->select('ip_address', 'action', 'entity_type', 'entity_id', 'meta', 'created_at')
            ->orderBy('created_at')
            ->get();

        if ($rows->isEmpty()) {
            return collect();
        }

        $tenantIdCache = [];
        $resolveTenantId = function (string $action, ?int $entityId, ?array $meta) use (&$tenantIdCache): ?int {
            if ($action === 'auth.login') {
                $key = "user:{$entityId}";
                return $tenantIdCache[$key] ??= User::find($entityId)?->tenant_id;
            }

            $slug = $meta['tenant_slug'] ?? null;
            if (!$slug) {
                return null;
            }

            $key = "slug:{$slug}";
            return $tenantIdCache[$key] ??= Tenant::where('slug', $slug)->value('id');
        };

        $byIp = $rows->groupBy('ip_address');
        $verifiedIps = VerifiedIp::pluck('ip_address')->flip();

        $results = collect();

        foreach ($byIp as $ip => $ipRows) {
            /** @var array<string, array{type:string,id:int,tenant_id:?int,last_seen:string}> $accounts */
            $accounts = [];

            foreach ($ipRows as $row) {
                $meta = json_decode($row->meta ?? '{}', true) ?: [];
                $tenantId = $resolveTenantId($row->action, $row->entity_id, $meta);
                $type = match ($row->action) {
                    'auth.login'     => 'Staff',
                    'customer.login' => 'Customer',
                    'renter.login'   => 'Renter',
                };
                $key = "{$type}:{$row->entity_id}";

                $accounts[$key] = [
                    'type'      => $type,
                    'id'        => $row->entity_id,
                    'tenant_id' => $tenantId,
                    'last_seen' => $row->created_at,
                ];
            }

            if (count($accounts) < 2) {
                continue; // one account on this IP — nothing to see here
            }

            $tenantCounts = collect($accounts)->countBy('tenant_id');
            $distinctTenants = $tenantCounts->keys()->filter()->count();
            $sameTenantMax = (int) $tenantCounts->max();

            $results->push([
                'ip_address'       => $ip,
                'total_accounts'   => count($accounts),
                'distinct_tenants' => $distinctTenants,
                'same_tenant_max'  => $sameTenantMax,
                'is_flagged'       => $distinctTenants > self::CROSS_TENANT_THRESHOLD,
                'is_verified'      => $verifiedIps->has($ip),
                'last_seen'        => collect($accounts)->max('last_seen'),
                'accounts'         => array_values($accounts),
            ]);
        }

        $results = self::withLabels($results);

        return $results->sortBy([
            [fn ($r) => (int) $r['is_flagged'], 'desc'],
            [fn ($r) => $r['distinct_tenants'], 'desc'],
            [fn ($r) => $r['total_accounts'], 'desc'],
        ])->values();
    }

    /** Only resolves names for accounts that made the final (already-filtered) list. */
    private static function withLabels(Collection $results): Collection
    {
        $labelCache = [];
        $tenantNameCache = [];

        $resolveLabel = function (string $type, int $id) use (&$labelCache): string {
            $key = "{$type}:{$id}";
            if (isset($labelCache[$key])) {
                return $labelCache[$key];
            }

            $model = match ($type) {
                'Staff'    => User::find($id),
                'Customer' => Customer::find($id),
                'Renter'   => Renter::find($id),
            };

            return $labelCache[$key] = $model?->name ?? "{$type} #{$id}";
        };

        $resolveTenantName = function (?int $tenantId) use (&$tenantNameCache): ?string {
            if (!$tenantId) {
                return null;
            }

            return $tenantNameCache[$tenantId] ??= Tenant::find($tenantId)?->name;
        };

        return $results->map(function ($r) use ($resolveLabel, $resolveTenantName) {
            $r['accounts'] = array_map(function ($account) use ($resolveLabel, $resolveTenantName) {
                $account['label'] = $resolveLabel($account['type'], $account['id']);
                $account['tenant_name'] = $resolveTenantName($account['tenant_id']);
                return $account;
            }, $r['accounts']);

            return $r;
        });
    }

    /**
     * Cheap enough to call from the sidebar on every page load for admins —
     * the actual 90-day scan behind it is cached, not run per-request.
     */
    public static function flaggedUnverifiedCount(): int
    {
        return Cache::remember('ip_reputation:flagged_count', now()->addMinutes(15), function () {
            return self::ipsWithSharedAccounts()
                ->filter(fn ($r) => $r['is_flagged'] && !$r['is_verified'])
                ->count();
        });
    }
}
