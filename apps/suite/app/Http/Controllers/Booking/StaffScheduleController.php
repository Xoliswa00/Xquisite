<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Models\Staff;
use App\Modules\Booking\Models\StaffBlock;
use App\Modules\Booking\Models\StaffSchedule;
use App\Services\Notifications\BookingNotificationService;
use Illuminate\Http\Request;

class StaffScheduleController extends Controller
{
    private const DAYS = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    public function edit(Staff $staff)
    {
        $staff->load(['schedules', 'blocks' => fn ($q) => $q->where('ends_at', '>', now())->orderBy('starts_at')]);

        // Build a keyed map: day_of_week => schedule (or null if not set)
        $scheduleByDay = collect(array_keys(self::DAYS))->mapWithKeys(function ($day) use ($staff) {
            $schedule = $staff->schedules->firstWhere('day_of_week', $day);
            return [$day => $schedule];
        });

        return view('staff.schedule', compact('staff', 'scheduleByDay'));
    }

    public function update(Request $request, Staff $staff, BookingNotificationService $notifications)
    {
        // Base structural rules
        $request->validate([
            'days'         => 'nullable|array',
            'days.*'       => 'integer|between:0,6',
            'start_time'   => 'required|array',
            'start_time.*' => 'required|date_format:H:i',
            'end_time'     => 'required|array',
            'end_time.*'   => 'required|date_format:H:i',
        ]);

        // Validate each day's pair individually — wildcard after: can't resolve array keys
        foreach (array_keys(self::DAYS) as $day) {
            $start = $request->input("start_time.{$day}");
            $end   = $request->input("end_time.{$day}");
            if ($start && $end && $end <= $start) {
                return back()->withInput()->withErrors([
                    "end_time.{$day}" => 'End time must be after start time for ' . self::DAYS[$day] . '.',
                ]);
            }
        }

        $activeDays = $request->input('days', []);

        // Upsert one row per day (0–6)
        foreach (array_keys(self::DAYS) as $day) {
            $isActive  = in_array((string) $day, array_map('strval', $activeDays));
            $startTime = $request->input("start_time.{$day}", '09:00');
            $endTime   = $request->input("end_time.{$day}", '17:00');

            StaffSchedule::updateOrCreate(
                ['staff_id' => $staff->id, 'day_of_week' => $day],
                [
                    'tenant_id'  => $staff->tenant_id,
                    'start_time' => $startTime,
                    'end_time'   => $endTime,
                    'is_active'  => $isActive,
                ]
            );
        }

        $notifications->notifyStaffScheduleChanged($staff, "Working hours were updated for {$staff->name}.");

        return redirect()->route('staff.show', $staff)
            ->with('success', 'Working hours saved.');
    }

    public function storeBlock(Request $request, Staff $staff, BookingNotificationService $notifications)
    {
        $validated = $request->validate([
            'starts_at' => 'required|date|after_or_equal:today',
            'ends_at'   => 'required|date|after:starts_at',
            'reason'    => 'nullable|string|max:255',
        ]);

        StaffBlock::create([
            'tenant_id' => $staff->tenant_id,
            'staff_id'  => $staff->id,
            'starts_at' => $validated['starts_at'],
            'ends_at'   => $validated['ends_at'],
            'reason'    => $validated['reason'] ?? null,
        ]);

        $notifications->notifyStaffScheduleChanged($staff, "A block was added for {$staff->name} from {$validated['starts_at']} to {$validated['ends_at']}.");

        return redirect()->route('staff.schedule', $staff)
            ->with('success', 'Time block added.');
    }

    public function destroyBlock(Staff $staff, StaffBlock $block, BookingNotificationService $notifications)
    {
        abort_if($block->staff_id !== $staff->id, 403);
        $block->delete();
        $notifications->notifyStaffScheduleChanged($staff, "A time block was removed for {$staff->name}.");

        return redirect()->route('staff.schedule', $staff)
            ->with('success', 'Time block removed.');
    }
}
