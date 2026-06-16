<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Models\Staff;
use Illuminate\Http\Request;

class StaffDashboardController extends Controller
{
    public function index(Request $request)
    {
        $now = now();

        $staffMembers = Staff::with([
            'schedules',
            'blocks'        => fn($q) => $q->where('ends_at', '>', $now)->orderBy('starts_at'),
            'appointments'  => fn($q) => $q->orderBy('scheduled_at'),
            'appointments.services',
        ])->orderBy('name')->get();

        $list = $staffMembers->map(function ($s) use ($now) {
            $status = 'Available';

            if (property_exists($s, 'is_active') && $s->is_active === false) {
                $status = 'Off duty';
            }

            // Check for active blocks
            $activeBlock = $s->blocks->first(fn($b) => $b->starts_at <= $now && $b->ends_at > $now);
            if ($activeBlock) {
                $status = 'Blocked';
            }

            // Current appointment heuristic: most recent appointment at or before now
            $currentAppt = $s->appointments->where('scheduled_at', '<=', $now)->sortByDesc('scheduled_at')->first();
            if ($currentAppt) {
                $minsSince = $currentAppt->scheduled_at->diffInMinutes($now);
                if ($minsSince >= 0 && $minsSince <= 180) {
                    $status = 'In appointment';
                }
            }

            // Next appointment
            $nextAppt = $s->appointments->where('scheduled_at', '>', $now)->sortBy('scheduled_at')->first();

            return [
                'id' => $s->id,
                'name' => $s->name,
                'email' => $s->email,
                'phone' => $s->phone,
                'status' => $status,
                'current_appointment' => $currentAppt,
                'next_appointment' => $nextAppt,
            ];
        });

        return view('staff.dashboard', ['staffList' => $list]);
    }
}
