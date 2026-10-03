<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\AppointmentInspirationPhoto;
use App\Services\Booking\InspirationPhotoService;
use App\Services\Tenant\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Serves and manages customer inspiration photos. The files sit on the
 * private disk, so every read goes through one of the show methods here,
 * which check ownership explicitly instead of relying on the HasTenant
 * scope having been applied before route-model binding ran.
 */
class InspirationPhotoController extends Controller
{
    private function resolveTenant(string $slug): Tenant
    {
        $tenant = Tenant::where('slug', $slug)->where('is_active', true)->firstOrFail();
        TenantContext::set($tenant->id);
        return $tenant;
    }

    /** 404 (not 403) for anything not this customer's, so ids can't be probed. */
    private function authorizeCustomer(Tenant $tenant, Appointment $appointment, ?AppointmentInspirationPhoto $photo = null): void
    {
        $customer = Auth::guard('customer')->user();

        abort_unless(
            $customer
            && (int) $appointment->tenant_id === (int) $tenant->id
            && (int) $appointment->customer_id === (int) $customer->id
            && (! $photo || (int) $photo->appointment_id === (int) $appointment->id),
            404
        );
    }

    private function stream(AppointmentInspirationPhoto $photo, string $size)
    {
        $path = $photo->pathFor($size);
        $disk = Storage::disk(AppointmentInspirationPhoto::DISK);
        abort_unless($disk->exists($path), 404);

        return $disk->response($path, null, [
            // Personal photo: never let a shared proxy or CDN keep a copy.
            'Cache-Control'          => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    // ── Customer portal (/book/{slug}/...) ─────────────────────────────────

    public function customerShow(string $slug, Appointment $appointment, AppointmentInspirationPhoto $photo, string $size)
    {
        $this->authorizeCustomer($this->resolveTenant($slug), $appointment, $photo);

        return $this->stream($photo, $size);
    }

    public function customerStore(string $slug, Appointment $appointment, Request $request, InspirationPhotoService $inspiration)
    {
        $this->authorizeCustomer($this->resolveTenant($slug), $appointment);

        if (! $appointment->inspirationIsEditable()) {
            return back()->withErrors(['inspiration_photos' => 'Inspiration photos can only be changed before the appointment.'])->withInput();
        }

        $slots = InspirationPhotoService::MAX_PER_APPOINTMENT - $appointment->inspirationPhotos()->count();

        $data = $request->validate(
            InspirationPhotoService::rules($slots, required: true)
                + ['inspiration_notes' => 'nullable|string|max:1000'],
            InspirationPhotoService::messages($slots)
        );

        $stored = $inspiration->store($appointment, $request->file('inspiration_photos', []));

        if ($request->filled('inspiration_notes')) {
            $appointment->update(['inspiration_notes' => $data['inspiration_notes']]);
        }

        if ($stored === 0) {
            return back()->withErrors(['inspiration_photos' => "We couldn't read that photo. Try a different one, or a screenshot of it."])->withInput();
        }

        return back()->with('success', $stored === 1 ? 'Inspiration photo added.' : "{$stored} inspiration photos added.");
    }

    public function customerDestroy(string $slug, Appointment $appointment, AppointmentInspirationPhoto $photo)
    {
        $this->authorizeCustomer($this->resolveTenant($slug), $appointment, $photo);

        if (! $appointment->inspirationIsEditable()) {
            return back()->withErrors(['inspiration_photos' => 'Inspiration photos can only be changed before the appointment.'])->withInput();
        }

        $photo->delete();

        return back()->with('success', 'Inspiration photo removed.');
    }

    // ── Staff dashboard (/appointments/...) ────────────────────────────────

    public function staffShow(Appointment $appointment, AppointmentInspirationPhoto $photo, string $size)
    {
        abort_unless(
            (int) $appointment->tenant_id === (int) auth()->user()->tenant_id
            && (int) $photo->appointment_id === (int) $appointment->id,
            404
        );

        return $this->stream($photo, $size);
    }
}
