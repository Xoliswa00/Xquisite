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
        DB::table('audit_logs')->insert([
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,

            'old_values' => $old ? json_encode($old) : null,
            'new_values' => $new ? json_encode($new) : null,

            'request_id' => app('request_id') ?? null,
            'ip_address' => request()->ip(),
            'url' => request()->fullUrl(),

            'meta' => json_encode($meta),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}