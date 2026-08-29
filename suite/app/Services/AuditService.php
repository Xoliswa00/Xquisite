<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

class AuditService
{
    public static function log(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $old = null,
        ?array $new = null,
        ?array $meta = []
    ) {
        $meta ??= [];

        [$userId, $actor] = self::resolveActor();
        if ($actor) {
            $meta['actor'] = $actor;
        }

        DB::table('audit_logs')->insert([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,

            'old_values' => $old ? json_encode($old) : null,
            'new_values' => $new ? json_encode($new) : null,

            'request_id' => app()->bound('request_id') ? app('request_id') : null,
            'ip_address' => request()->ip(),
            'url' => request()->fullUrl(),

            'meta' => json_encode($meta),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * The app has three auth guards (web/staff, customer, renter). `user_id`
     * stays reserved for staff so LogController's `with('user')` keeps
     * resolving against the users table — customer/renter actors are
     * recorded in meta instead so a public-portal action still traces back
     * to who did it.
     */
    private static function resolveActor(): array
    {
        if (auth('web')->check()) {
            return [auth('web')->id(), null];
        }

        if (auth('customer')->check()) {
            $customer = auth('customer')->user();
            return [null, [
                'type'  => 'Customer',
                'id'    => $customer->id,
                'label' => $customer->name ?? $customer->email,
            ]];
        }

        if (auth('renter')->check()) {
            $renter = auth('renter')->user();
            return [null, [
                'type'  => 'Renter',
                'id'    => $renter->id,
                'label' => $renter->name ?? $renter->email,
            ]];
        }

        if (auth('contractor')->check()) {
            $contractor = auth('contractor')->user();
            return [null, [
                'type'  => 'Contractor',
                'id'    => $contractor->id,
                'label' => $contractor->name ?? $contractor->email,
            ]];
        }

        return [null, null];
    }
}
