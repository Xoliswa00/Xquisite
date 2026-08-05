<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\Website\TenantSectionSeeder;
use Illuminate\Console\Command;

/**
 * Seeds tenant_page_sections for tenants who already have a live site (from
 * before the section builder existed) so their site keeps rendering after
 * Template.blade_view is flipped to the shared sections-page view.
 * Idempotent — seedForTenant() is a no-op for a tenant who already has
 * sections, so this is safe to re-run.
 */
class BackfillTenantSections extends Command
{
    protected $signature   = 'website:sections:backfill {--tenant= : Only backfill a single tenant by ID}';
    protected $description = 'Seed tenant_page_sections for tenants with an already-active, non-placeholder template';

    public function handle(): int
    {
        $query = Tenant::query()->whereHas('activeTemplate');

        if ($tenantId = $this->option('tenant')) {
            $query->where('id', $tenantId);
        }

        $tenants = $query->with('activeTemplate.template')->get();

        if ($tenants->isEmpty()) {
            $this->info('No tenants with an active template.');
            return self::SUCCESS;
        }

        $seeded = 0;

        foreach ($tenants as $tenant) {
            $template = $tenant->activeTemplate?->template;

            if (! $template) {
                continue;
            }

            $before = $tenant->pageSections()->count();
            TenantSectionSeeder::seedForTenant($tenant, $template);
            $after = $tenant->pageSections()->count();

            if ($after > $before) {
                $seeded++;
                $this->line("Seeded {$tenant->name} ({$template->key}): {$after} sections");
            }
        }

        $this->info("Backfilled {$seeded} of {$tenants->count()} tenant(s).");

        return self::SUCCESS;
    }
}
