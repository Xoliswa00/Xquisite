<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Booking\Models\Appointment;
use App\Services\Tenant\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerPortalController extends Controller
{
    private function resolveTenant(string $slug): Tenant
    {
        $tenant = Tenant::where('slug', $slug)->where('is_active', true)->firstOrFail();
        TenantContext::set($tenant->id);
        return $tenant;
    }

    private function requireCustomer(string $slug)
    {
        if (!Auth::guard('customer')->check()) {
            return redirect()->route('book.login', $slug);
        }
        return null;
    }

    public function myBookings(string $slug)
    {
        $tenant   = $this->resolveTenant($slug);
        $redirect = $this->requireCustomer($slug);
        if ($redirect) return $redirect;

        $customer = Auth::guard('customer')->user();

        $upcoming = Appointment::where('customer_id', $customer->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('scheduled_at', '>=', now())
            ->with(['service', 'staff'])
            ->orderBy('scheduled_at')
            ->get();

        $past = Appointment::where('customer_id', $customer->id)
            ->where(function ($q) {
                $q->whereIn('status', ['completed', 'cancelled', 'no_show'])
                  ->orWhere('scheduled_at', '<', now());
            })
            ->with(['service', 'staff'])
            ->orderByDesc('scheduled_at')
            ->limit(20)
            ->get();

        return view('booking.my-bookings', compact('tenant', 'slug', 'upcoming', 'past'));
    }

    public function cancel(string $slug, Appointment $appointment, Request $request)
    {
        $this->resolveTenant($slug);
        $redirect = $this->requireCustomer($slug);
        if ($redirect) return $redirect;

        $customer = Auth::guard('customer')->user();

        abort_if($appointment->customer_id !== $customer->id, 403);
        abort_if(!in_array($appointment->status, ['pending', 'confirmed']), 422, 'This appointment cannot be cancelled.');

        if ($appointment->scheduled_at->diffInHours(now(), false) > -2) {
            return back()->withErrors(['cancel' => 'Appointments must be cancelled at least 2 hours before the start time.']);
        }

        $appointment->update(['status' => 'cancelled']);

        return redirect()->route('book.my-bookings', $slug)
            ->with('success', 'Your appointment has been cancelled.');
    }
}
