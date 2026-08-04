<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill lowercase slug/subdomain so subdomain-based tenant resolution
     * (ResolveTenant::resolveSubdomainTenantId) matches reliably against
     * browser-normalized lowercase hostnames. Tenant::setSlugAttribute /
     * setSubdomainAttribute keep new writes lowercase going forward.
     */
    public function up(): void
    {
        $tenants = DB::table('tenants')->select('id', 'slug', 'subdomain')->get();

        foreach ($tenants as $tenant) {
            $updates = [];

            if ($tenant->slug !== null && $tenant->slug !== strtolower($tenant->slug)) {
                $lower = strtolower($tenant->slug);
                $collision = DB::table('tenants')->where('slug', $lower)->where('id', '!=', $tenant->id)->exists();

                if ($collision) {
                    $this->logSkippedCollision('slug', $tenant->id, $tenant->slug, $lower);
                } else {
                    $updates['slug'] = $lower;
                }
            }

            if ($tenant->subdomain !== null && $tenant->subdomain !== strtolower($tenant->subdomain)) {
                $lower = strtolower($tenant->subdomain);
                $collision = DB::table('tenants')->where('subdomain', $lower)->where('id', '!=', $tenant->id)->exists();

                if ($collision) {
                    $this->logSkippedCollision('subdomain', $tenant->id, $tenant->subdomain, $lower);
                } else {
                    $updates['subdomain'] = $lower;
                }
            }

            if (!empty($updates)) {
                DB::table('tenants')->where('id', $tenant->id)->update($updates);
            }
        }
    }

    public function down(): void
    {
        // Casing normalization is not reversible (original casing isn't recorded).
    }

    private function logSkippedCollision(string $column, int $tenantId, string $original, string $lower): void
    {
        try {
            DB::table('system_logs')->insert([
                'level'      => 'WARNING',
                'message'    => "normalize_tenant_slug_and_subdomain_casing: skipped lowercasing tenants.{$column} for tenant {$tenantId} — '{$lower}' already in use by another tenant",
                'context'    => json_encode([
                    'tenant_id' => $tenantId,
                    'column'    => $column,
                    'original'  => $original,
                    'lower'     => $lower,
                ]),
                'user_id'    => null,
                'ip_address' => null,
                'url'        => null,
                'status'     => 'new',
                'source'     => 'migration',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable) {
            // Never let logging break the migration
        }
    }
};
