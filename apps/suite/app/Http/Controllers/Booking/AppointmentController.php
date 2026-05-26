<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Mail\AppointmentConfirmationEmail;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Customer;
use App\Modules\Booking\Models\Staff;
use App\Modules\Booking\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with(['customer', 'staff', 'service'])
            ->orderByDesc('scheduled_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

    public function store(Request $request)
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

        $appointment = Appointment::create($data);

        // Send confirmation email if the customer has an email and appointment is confirmed
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

    public function update(Request $request, Appointment $appointment)
    {
        $data = $request->validate([
            'customer_id'      => 'required|exists:customers,id',
            'staff_id'         => 'required|exists:staff,id',
            'service_id'       => 'required|exists:services,id',
            'scheduled_at'     => 'required|date',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'status'           => 'required|in:pending,confirmed,completed,cancelled,no_show',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $appointment->update($data);

        return redirect()->route('appointments.show', $appointment)
            ->with('success', 'Appointment updated.');
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return redirect()->route('appointments.index')
            ->with('success', 'Appointment deleted.');
    }
}
