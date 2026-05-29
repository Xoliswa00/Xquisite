<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Mail\AppointmentConfirmationEmail;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Customer;
use App\Modules\Booking\Models\Staff;
use App\Modules\Booking\Models\Service;
use App\Services\Booking\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with(['customer', 'staff', 'service'])
            ->orderByDesc('scheduled_at');

        if ($request->filled('status')) {
            if ($request->status === 'unassigned') {
                $query->whereNull('staff_id');
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('date')) {
            $query->whereDate('scheduled_at', $request->date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('customer', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $appointments = $query->paginate(15)->withQueryString();

        return view('appointments.index', compact('appointments'));
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $staff     = Staff::where('is_active', true)->orderBy('name')->get();
        $services  = Service::where('is_active', true)->orderBy('name')->get();

        return view('appointments.create', compact('customers', 'staff', 'services'));
    }

    public function store(Request $request, AvailabilityService $availability)
    {
        $data = $request->validate([
            'customer_id'      => 'required|exists:customers,id',
            'staff_id'         => 'required|exists:staff,id',
            'service_id'       => 'required|exists:services,id',
            'scheduled_at'     => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'status'           => 'required|in:pending,confirmed,completed,cancelled,no_show',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $staff  = Staff::findOrFail($data['staff_id']);
        $start  = Carbon::parse($data['scheduled_at']);
        $error  = $availability->check($staff, $start, (int) $data['duration_minutes']);

        if ($error) {
            return back()->withInput()->withErrors(['scheduled_at' => $error]);
        }

        $appointment = Appointment::create($data);

        if (in_array($data['status'], ['confirmed', 'pending'])) {
            $appointment->load(['customer', 'staff', 'service']);
            if ($appointment->customer->email) {
                Mail::to($appointment->customer->email)
                    ->queue(new AppointmentConfirmationEmail($appointment));
            }
        }

        return redirect()->route('appointments.index')
            ->with('success', 'Appointment booked successfully.');
    }

    public function show(Appointment $appointment)
    {
        $appointment->load(['customer', 'staff', 'service', 'reminders']);

        return view('appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment)
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $staff     = Staff::where('is_active', true)->orderBy('name')->get();
        $services  = Service::where('is_active', true)->orderBy('name')->get();

        return view('appointments.edit', compact('appointment', 'customers', 'staff', 'services'));
    }

    public function update(Request $request, Appointment $appointment, AvailabilityService $availability)
    {
        $data = $request->validate([
            'customer_id'      => 'required|exists:customers,id',
            'staff_id'         => 'nullable|exists:staff,id',
            'service_id'       => 'required|exists:services,id',
            'scheduled_at'     => 'required|date',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'status'           => 'required|in:pending,confirmed,completed,cancelled,no_show',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $slotChanged     = $appointment->scheduled_at->toDateTimeString() !== Carbon::parse($data['scheduled_at'])->toDateTimeString();
        $staffChanged    = $appointment->staff_id !== (int) ($data['staff_id'] ?? 0);
        $durationChanged = $appointment->duration_minutes !== (int) $data['duration_minutes'];

        // Rescheduling clears staff assignment — they must be re-assigned
        if ($slotChanged || $durationChanged) {
            $data['staff_id'] = null;
        }

        // If staff is explicitly assigned (not a reschedule), run availability check
        if (!$slotChanged && !$durationChanged && $data['staff_id']) {
            $staff = Staff::findOrFail($data['staff_id']);
            $start = Carbon::parse($data['scheduled_at']);
            $error = $availability->check($staff, $start, (int) $data['duration_minutes'], $appointment->id);

            if ($error) {
                return back()->withInput()->withErrors(['staff_id' => $error]);
            }
        }

        $appointment->update($data);

        return redirect()->route('appointments.show', $appointment)
            ->with('success', $slotChanged || $durationChanged
                ? 'Appointment rescheduled. Staff assignment cleared — please assign a staff member.'
                : 'Appointment updated.'
            );
    }

    /** Admin assigns a staff member to an unassigned appointment */
    public function assign(Request $request, Appointment $appointment, AvailabilityService $availability)
    {
        $data = $request->validate([
            'staff_id' => 'required|exists:staff,id',
        ]);

        $staff = Staff::findOrFail($data['staff_id']);
        $error = $availability->check($staff, $appointment->scheduled_at, $appointment->duration_minutes, $appointment->id);

        if ($error) {
            return back()->withErrors(['staff_id' => $error]);
        }

        $appointment->update([
            'staff_id' => $staff->id,
            'status'   => $appointment->status === 'pending' ? 'confirmed' : $appointment->status,
        ]);

        return redirect()->route('appointments.show', $appointment)
            ->with('success', "{$staff->name} assigned. Appointment confirmed.");
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return redirect()->route('appointments.index')
            ->with('success', 'Appointment deleted.');
    }
}
