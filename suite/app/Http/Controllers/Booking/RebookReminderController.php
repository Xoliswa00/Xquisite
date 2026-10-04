<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Booking\Models\Customer;
use App\Services\Booking\ConsentLedger;
use App\Services\Tenant\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Lets a client turn "time to rebook" messages off (or back on): from My
 * Bookings while signed in, or with one click from the email itself via a
 * signed link (no login, so the opt-out is never harder than the message).
 */
class RebookReminderController extends Controller
{
    public function __construct(private readonly ConsentLedger $ledger) {}

    private function resolveTenant(string $slug): Tenant
    {
        $tenant = Tenant::where('slug', $slug)->where('is_active', true)->firstOrFail();
        TenantContext::set($tenant->id);

        return $tenant;
    }

    public function toggle(string $slug, Request $request)
    {
        $this->resolveTenant($slug);
        $customer = Auth::guard('customer')->user();
        abort_unless($customer, 404);

        $on = $request->boolean('on');
        $this->ledger->setRebookReminders($customer, $on);

        return back()->with('success', $on ? 'Rebook reminders are on.' : "Rebook reminders are off. We won't send them any more.");
    }

    public function optOut(string $slug, Customer $customer)
    {
        $tenant = $this->resolveTenant($slug);
        abort_unless((int) $customer->tenant_id === (int) $tenant->id, 404);

        $this->ledger->setRebookReminders($customer, false);

        return view('booking.rebook-opt-out', compact('tenant', 'slug', 'customer'));
    }
}
