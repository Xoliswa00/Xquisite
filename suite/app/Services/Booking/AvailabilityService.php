<?php

namespace App\Services\Booking;

use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Service;
use App\Modules\Booking\Models\Staff;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AvailabilityService
{
    /**
     * Hard stops only — working hours and blocked periods.
     * Returns an error string, or null if clear.
     */
    public function checkHardStop(Staff $staff, Carbon $start, int $duration): ?string
    {
        $end = $start->copy()->addMinutes($duration);

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

        return null;
    }

    /**
     * Returns existing appointments that overlap the given slot (soft conflicts).
     * Does not block — caller decides what to do with these.
     */
    public function getConflicts(Staff $staff, Carbon $start, int $duration, ?int $excludeId = null): Collection
    {
        $end      = $start->copy()->addMinutes($duration);
        $dayStart = $start->copy()->startOfDay();
        $dayEnd   = $start->copy()->endOfDay();

        $sameDay = Appointment::where('staff_id', $staff->id)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->whereBetween('scheduled_at', [$dayStart, $dayEnd])
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->with('customer')
            ->get();

        return $sameDay->filter(function ($existing) use ($start, $end) {
            $existingEnd = $existing->scheduled_at->copy()->addMinutes($existing->duration_minutes);
            return $existing->scheduled_at->lt($end) && $existingEnd->gt($start);
        })->values();
    }

    /**
     * Full hard check (working hours + blocks + double-booking).
     * Used by the public booking portal where overlaps must be blocked.
     */
    public function check(Staff $staff, Carbon $start, int $duration, ?int $excludeId = null): ?string
    {
        $hardStop = $this->checkHardStop($staff, $start, $duration);
        if ($hardStop) {
            return $hardStop;
        }

        $conflicts = $this->getConflicts($staff, $start, $duration, $excludeId);
        if ($conflicts->isNotEmpty()) {
            $existing    = $conflicts->first();
            $existingEnd = $existing->scheduled_at->copy()->addMinutes($existing->duration_minutes);
            return "{$staff->name} already has an appointment from "
                . $existing->scheduled_at->format('H:i') . ' to ' . $existingEnd->format('H:i') . '.';
        }

        return null;
    }

    /**
     * Build a human-readable conflict warning string for admin create/update/assign.
     */
    public function buildConflictWarning(Staff $staff, Carbon $start, int $duration, Collection $conflicts): string
    {
        $newEnd = $start->copy()->addMinutes($duration);

        $details = $conflicts->map(function ($c) use ($start, $newEnd) {
            $cEnd        = $c->scheduled_at->copy()->addMinutes($c->duration_minutes);
            $overlapMins = max($start, $c->scheduled_at)->diffInMinutes(min($newEnd, $cEnd));
            return "{$c->customer->name} ({$c->scheduled_at->format('H:i')}–{$cEnd->format('H:i')}, {$overlapMins} min overlap)";
        })->join('; ');

        return "Overlap on {$staff->name}'s schedule: {$details}.";
    }

    /**
     * Generate all available time slots for a staff member on a given date.
     *
     * @param  Staff            $staff
     * @param  Carbon           $date      The date to generate slots for (time is ignored)
     * @param  int              $duration  Duration in minutes
     * @return Collection<Carbon>
     */
    public function availableSlots(Staff $staff, Carbon $date, int $duration): Collection
    {
        $now = now();

        $schedule = $staff->schedules()
            ->where('day_of_week', $date->dayOfWeek)
            ->where('is_active', true)
            ->first();

        if (!$schedule) {
            return collect();
        }

        $workStart = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->start_time);
        $workEnd   = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->end_time);

        $booked = Appointment::where('staff_id', $staff->id)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->whereBetween('scheduled_at', [$workStart, $workEnd])
            ->get();

        $blocks = $staff->blocks()
            ->where('starts_at', '<', $workEnd)
            ->where('ends_at', '>', $workStart)
            ->get();

        $slots  = collect();
        $cursor = $workStart->copy();

        while ($cursor->copy()->addMinutes($duration)->lte($workEnd)) {
            $slotEnd = $cursor->copy()->addMinutes($duration);

            if ($slotEnd->lte($now)) {
                $cursor->addMinutes(15);
                continue;
            }

            $blockedBy = $blocks->first(
                fn($block) => $block->starts_at->lt($slotEnd) && $block->ends_at->gt($cursor)
            );

            $bookedBy = $booked->first(function ($appt) use ($cursor, $slotEnd) {
                $apptEnd = $appt->scheduled_at->copy()->addMinutes($appt->duration_minutes);
                return $appt->scheduled_at->lt($slotEnd) && $apptEnd->gt($cursor);
            });

            if (!$blockedBy && !$bookedBy) {
                $slots->push($cursor->copy());
            }

            $cursor->addMinutes(15);
        }

        return $slots;
    }

    /**
     * Generate available slots for a single service on a given date.
     * A slot is available if at least one active staff member assigned
     * to that service can cover the full duration.
     *
     * @return Collection<Carbon>
     */
    public function availableSlotsForService(Service $service, Carbon $date): Collection
    {
        return $this->availableSlotsForDuration(
            $service->duration_minutes,
            $date,
            [$service->id]
        );
    }

    /**
     * Generate available slots for a combined duration across multiple services.
     * A slot is available if at least one active staff member — who is linked
     * to at least one of the selected services — can cover the full duration.
     * Used by the public booking portal when multiple services are selected.
     *
     * @param  int              $totalDuration  Combined minutes of all selected services
     * @param  Carbon           $date           The date to check (time is ignored)
     * @param  array            $serviceIds     Filter staff to those linked to these services
     * @return Collection<Carbon>
     */
    public function availableSlotsForDuration(int $totalDuration, Carbon $date, array $serviceIds = []): Collection
    {
        $staffQuery = Staff::where('is_active', true)->with(['schedules', 'blocks']);

        if (!empty($serviceIds)) {
            $staffQuery->whereHas('services', fn($q) => $q->whereIn('services.id', $serviceIds));
        }

        $staff = $staffQuery->get();

        if ($staff->isEmpty()) {
            return collect();
        }

        // Union: slot is offered if at least one staff member is free for the full duration
        $allSlots = collect();

        foreach ($staff as $member) {
            $memberSlots = $this->availableSlots($member, $date, $totalDuration);
            foreach ($memberSlots as $slot) {
                $key = $slot->format('Y-m-d H:i');
                if (!$allSlots->has($key)) {
                    $allSlots->put($key, $slot);
                }
            }
        }

        return $allSlots->values()->sortBy(fn($s) => $s->timestamp)->values();
    }
}
