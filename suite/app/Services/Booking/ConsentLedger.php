<?php

namespace App\Services\Booking;

use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Customer;
use App\Modules\Booking\Models\CustomerConsent;

/**
 * The single write path for a client's consent history. Every change to
 * something the client was told about or agreed to goes through here, so the
 * history is complete enough to answer "when did this client say yes/no?".
 */
class ConsentLedger
{
    public function record(
        Customer $customer,
        string $scope,
        string $action,
        string $via,
        ?Appointment $appointment = null,
    ): CustomerConsent {
        return CustomerConsent::create([
            'tenant_id'      => $customer->tenant_id,
            'customer_id'    => $customer->id,
            'appointment_id' => $appointment?->id,
            'scope'          => $scope,
            'action'         => $action,
            'via'            => $via,
            'ip'             => $via === 'client' ? request()?->ip() : null,
        ]);
    }

    /** Client allows / stops the business sharing a saved look. */
    public function setShowcase(Appointment $look, bool $allowed): void
    {
        if ($allowed === ($look->look_showcase_at !== null)) {
            return; // no change, no history line
        }

        $look->update(['look_showcase_at' => $allowed ? now() : null]);
        $this->record($look->customer, CustomerConsent::SCOPE_LOOK_SHOWCASE,
            $allowed ? CustomerConsent::GRANTED : CustomerConsent::WITHDRAWN, 'client', $look);
    }

    /** Client turns rebook reminders off (or back on). */
    public function setRebookReminders(Customer $customer, bool $on): void
    {
        if ($on === ($customer->rebook_reminders_opt_out_at === null)) {
            return;
        }

        $customer->update(['rebook_reminders_opt_out_at' => $on ? null : now()]);
        $this->record($customer, CustomerConsent::SCOPE_REBOOK_REMINDERS,
            $on ? CustomerConsent::GRANTED : CustomerConsent::WITHDRAWN, 'client');
    }
}
