<?php

namespace App\Services\Booking;

use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Staff;
use Carbon\Carbon;

class AvailabilityService
{
    /**
     * Check if a staff member is available for a given slot.
     *
     * @param  Staff        $staff
     * @param  Carbon       $start       Appointment start time
     * @param  int          $duration    Duration in minutes
     * @param  int|null     $excludeId   Appointment ID to exclude (for edits)
     * @return string|null  Error message, or null if available
     */
    public function check(Staff $staff, Carbon $start, int $duration, ?int $excludeId = null): ?string
    {
        $end = $start->copy()->addMinutes($duration);

        // 1. Check working hours
        $schedule = $staff->schedules()
            ->where('day_of_week', $start->dayOfWeek)
            ->where('is_active', true)
            ->first();

        if (!$schedule) {
            return "{$staff->name} does not work on " . $start->format('l') . 's.';
        }

        $workStart = Carbon::parse($start->format('Y-m-d') . ' ' . $schedule->start_time);
        $workEnd   = Carbon::parse($start->format('Y-m-d') . ' ' . $schedule->end_time);

        if ($start->lt($workStart) || $end->gt($workEnd)) {
            return "{$staff->name}'s working hours on " . $start->format('l') . ' are '
                . $workStart->format('H:i') . '–' . $workEnd->format('H:i') . '.';
        }

        // 2. Check blocked periods
        $blocked = $staff->blocks()
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->first();

        if ($blocked) {
            $reason = $blocked->reason ? " ({$blocked->reason})" : '';
            return "{$staff->name} is unavailable from "
                . $blocked->starts_at->format('d M H:i') . ' to '
                . $blocked->ends_at->format('d M H:i') . $reason . '.';
        }

        // 3. Check double-booking — fetch same-day appointments, check overlap in PHP
        // (database-agnostic: avoids DATE_ADD which is MySQL-only)
        $dayStart = $start->copy()->startOfDay();
        $dayEnd   = $start->copy()->endOfDay();

        $sameDay = Appointment::where('staff_id', $staff->id)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->whereBetween('scheduled_at', [$dayStart, $dayEnd])
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->get();

        foreach ($sameDay as $existing) {
            $existingEnd = $existing->scheduled_at->copy()->addMinutes($existing->duration_minutes);
            // Overlap when: existing.start < new.end AND existing.end > new.start
            if ($existing->scheduled_at->lt($end) && $existingEnd->gt($start)) {
                return "{$staff->name} already has an appointment from "
                    . $existing->scheduled_at->format('H:i') . ' to ' . $existingEnd->format('H:i') . '.';
            }
        }

        return null;
    }
}
