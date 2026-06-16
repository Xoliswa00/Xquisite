<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Database\Seeders\DemoSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetDemo extends Command
{
    protected $signature   = 'demo:reset {--force : Skip confirmation}';
    protected $description = 'Wipe and re-seed the demo tenant data';

    public function handle(): int
    {
        $tenant = Tenant::where('is_demo', true)->first();

        if (! $tenant) {
            $this->error('No demo tenant found. Run db:seed --class=DemoSeeder first.');
            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm("Reset demo tenant [{$tenant->name}]? This will delete all demo data.")) {
            $this->info('Cancelled.');
            return self::SUCCESS;
        }

        $this->info('Resetting demo tenant...');

        DB::transaction(function () use ($tenant) {
            // Wipe module-specific data for the demo tenant
            // Orders of deletion respect foreign key constraints
            $tenantId = $tenant->id;
            $userIds  = $tenant->users()->pluck('id');

            // Booking data
            DB::table('appointments')->whereIn('staff_id', function ($q) use ($tenantId) {
                $q->select('id')->from('staff')->where('tenant_id', $tenantId);
            })->delete();
            DB::table('staff_blocks')->whereIn('staff_id', function ($q) use ($tenantId) {
                $q->select('id')->from('staff')->where('tenant_id', $tenantId);
            })->delete();
            DB::table('staff_schedules')->whereIn('staff_id', function ($q) use ($tenantId) {
                $q->select('id')->from('staff')->where('tenant_id', $tenantId);
            })->delete();
            DB::table('staff')->where('tenant_id', $tenantId)->delete();
            DB::table('customers')->where('tenant_id', $tenantId)->delete();
            DB::table('services')->where('tenant_id', $tenantId)->delete();

            // POS data
            DB::table('sale_items')->whereIn('sale_id', function ($q) use ($tenantId) {
                $q->select('id')->from('sales')->where('tenant_id', $tenantId);
            })->delete();
            DB::table('sales')->where('tenant_id', $tenantId)->delete();
            DB::table('stock_movements')->whereIn('product_id', function ($q) use ($tenantId) {
                $q->select('id')->from('products')->where('tenant_id', $tenantId);
            })->delete();
            DB::table('products')->where('tenant_id', $tenantId)->delete();
            DB::table('suppliers')->where('tenant_id', $tenantId)->delete();

            // Property data
            DB::table('maintenance_requests')->whereIn('unit_id', function ($q) use ($tenantId) {
                $q->select('id')->from('units')->whereIn('property_id', function ($q2) use ($tenantId) {
                    $q2->select('id')->from('properties')->where('tenant_id', $tenantId);
                });
            })->delete();
            DB::table('rent_payments')->whereIn('lease_id', function ($q) use ($tenantId) {
                $q->select('id')->from('leases')->where('tenant_id', $tenantId);
            })->delete();
            DB::table('leases')->where('tenant_id', $tenantId)->delete();
            DB::table('renters')->where('tenant_id', $tenantId)->delete();
            DB::table('units')->whereIn('property_id', function ($q) use ($tenantId) {
                $q->select('id')->from('properties')->where('tenant_id', $tenantId);
            })->delete();
            DB::table('properties')->where('tenant_id', $tenantId)->delete();
        });

        // Re-seed the demo tenant baseline
        $this->call('db:seed', ['--class' => DemoSeeder::class, '--force' => true]);

        $this->info('Demo tenant reset successfully.');

        return self::SUCCESS;
    }
}
