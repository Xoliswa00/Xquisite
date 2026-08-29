<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Modules\Property\Models\Lease;
use App\Notifications\LeaseExpiringNotification;
use Illuminate\Console\Command;

class CheckExpiringLeases extends Command
{
    protected $signature = 'leases:check-expiring';

    protected $description = 'Notify tenant owners about active leases ending soon (30/14/7/1 days out)';

    /**
     * Milestones a lease should be notified at. Checked as "days left <= threshold
     * and not yet sent" rather than an exact-date match, so a scheduler gap (downtime,
     * a deploy, an overlapping run being skipped) doesn't cause a lease to silently
     * miss a milestone forever — the next run just catches up.
     */
    private const THRESHOLDS = [30, 14, 7, 1];

    public function handle(): int
    {
        $notified = 0;
        $ownerCache = [];

        $leases = Lease::where('status', 'active')
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now()->startOfDay(), now()->addDays(max(self::THRESHOLDS))->endOfDay()])
            ->with(['property', 'unit', 'renter'])
            ->get();

        foreach ($leases as $lease) {
            $daysLeft = now()->startOfDay()->diffInDays($lease->end_date->startOfDay(), false);
            $alreadySent = $lease->notified_thresholds ?? [];

            // Thresholds the lease has now passed (days left <= threshold) that haven't
            // been sent yet. If a gap caused several to be crossed at once, that's still
            // just one real-world situation — send a single email reflecting the true
            // days left, not one stale email per missed milestone, and mark them all handled.
            $crossed = array_filter(self::THRESHOLDS, fn ($t) => $daysLeft <= $t && !in_array($t, $alreadySent, true));

            if (!empty($crossed)) {
                if (!array_key_exists($lease->tenant_id, $ownerCache)) {
                    $ownerCache[$lease->tenant_id] = $lease->tenant_id ? Tenant::find($lease->tenant_id)?->owner() : null;
                }
                $owner = $ownerCache[$lease->tenant_id];

                if ($owner) {
                    $owner->notify(new LeaseExpiringNotification($lease, max(0, $daysLeft)));
                    $notified++;
                }

                $lease->update(['notified_thresholds' => array_values(array_unique([...$alreadySent, ...$crossed]))]);
            }
        }

        $this->info("{$notified} lease-expiry notification(s) sent.");

        return Command::SUCCESS;
    }
}
