<?php
namespace App\Modules\Booking\Observers;

use App\Modules\Booking\Models\Appointment;
use App\Services\AuditService;

class AppointmentObserver
{
    public function created(Appointment $appointment)
    {
        AuditService::log(
            action: 'appointment.created',
            entityType: Appointment::class,
            entityId: $appointment->id,
            new: $appointment->toArray()
        );
    }

    public function updated(Appointment $appointment)
    {
        AuditService::log(
            action: 'appointment.updated',
            entityType: Appointment::class,
            entityId: $appointment->id,
            old: $appointment->getOriginal(),
            new: $appointment->getChanges()
        );
    }

    public function deleted(Appointment $appointment)
    {
        AuditService::log(
            action: 'appointment.deleted',
            entityType: Appointment::class,
            entityId: $appointment->id,
            old: $appointment->toArray()
        );
    }
}