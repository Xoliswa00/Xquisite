<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\AppointmentInspirationPhoto;
use App\Services\Booking\InspirationPhotoService;
use App\Services\Notifications\BookingNotificationService;
use App\Services\Tenant\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Look photos on a booking, for both sides:
 *  - customer: view, add and remove their inspiration photos; remove a saved
 *    look; start "Book this look again" (read back by PublicBookingController);
 *  - staff: view, add and remove after photos; save or unsave the client's look.
 * The files sit on the private disk, so every read goes through one of the
 * show methods here, which check ownership explicitly instead of relying on
 * the HasTenant scope having been applied before route-model binding ran.
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

    public function customerStore(string $slug, Appointment $appointment, Request $request, InspirationPhotoService $inspiration, BookingNotificationService $notifications)
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

        $notifications->notifyInspirationAdded($appointment, $stored);

        return back()->with('success', $stored === 1 ? 'Inspiration photo added.' : "{$stored} inspiration photos added.");
    }

    public function customerDestroy(string $slug, Appointment $appointment, AppointmentInspirationPhoto $photo)
    {
        $this->authorizeCustomer($this->resolveTenant($slug), $appointment, $photo);
        // "After" photos belong to the business's saved look, not the customer's upload.
        abort_if($photo->isResult(), 404);

        if (! $appointment->inspirationIsEditable()) {
            return back()->withErrors(['inspiration_photos' => 'Inspiration photos can only be changed before the appointment.'])->withInput();
        }

        $photo->delete();

        return back()->with('success', 'Inspiration photo removed.');
    }

    /**
     * Customer removes a saved look. The business's after photos of them are
     * deleted now (not left for the prune), their own uploads fall back to the
     * normal 90-day rule, and the removal is recorded so staff can't re-save it.
     */
    public function customerForgetLook(string $slug, Appointment $appointment)
    {
        $this->authorizeCustomer($this->resolveTenant($slug), $appointment);

        $appointment->resultPhotos()->get()->each->delete();
        $appointment->update(['look_saved_at' => null, 'look_removed_at' => now()]);

        return back()->with('success', 'Saved look removed. The after photos have been deleted.');
    }

    /**
     * "Book this look again": remember which look to copy (with the services it
     * was for, and when), then start a booking for the same services. The
     * confirm step only offers it again for a matching booking, within
     * REBOOK_WINDOW_MINUTES, so an abandoned rebook can't leak into a later,
     * unrelated booking.
     */
    public function customerRebookLook(string $slug, Appointment $appointment)
    {
        $this->authorizeCustomer($this->resolveTenant($slug), $appointment);
        abort_unless($appointment->isLookSaved(), 404);

        $services   = $appointment->services()->get(['services.id', 'services.name', 'services.is_active']);
        $active     = $services->where('is_active', true);
        $serviceIds = $active->pluck('id')->values()->all();

        session(['rebook_look' => [
            'id'          => $appointment->id,
            'service_ids' => $serviceIds,
            'at'          => now()->timestamp,
        ]]);

        if ($serviceIds === []) {
            return redirect()->route('book.index', $slug)
                ->with('info', "The services from this look aren't on offer any more. Pick what you'd like, and your saved look will be attached.");
        }

        $redirect = redirect()->route('book.service', ['slug' => $slug, 'service_ids' => $serviceIds]);
        $dropped  = $services->where('is_active', false)->pluck('name');

        return $dropped->isEmpty()
            ? $redirect
            : $redirect->with('info', $dropped->join(', ', ' and ') . " isn't offered any more, so we left it out.");
    }

    // ── Staff dashboard (/appointments/...) ────────────────────────────────

    private function authorizeStaff(Appointment $appointment, ?AppointmentInspirationPhoto $photo = null): void
    {
        abort_unless(
            (int) $appointment->tenant_id === (int) auth()->user()->tenant_id
            && (! $photo || (int) $photo->appointment_id === (int) $appointment->id),
            404
        );
    }

    /** Staff add "after" photos (how it turned out) to a booking. */
    public function staffStoreResults(Appointment $appointment, Request $request, InspirationPhotoService $inspiration)
    {
        $this->authorizeStaff($appointment);

        if (! $appointment->lookCanBeRecorded()) {
            return back()->withErrors(['inspiration_photos' => 'After photos can be added once the appointment has happened.']);
        }

        $slots = InspirationPhotoService::MAX_PER_APPOINTMENT - $appointment->resultPhotos()->count();
        $request->validate(
            InspirationPhotoService::rules($slots, required: true),
            InspirationPhotoService::messages($slots)
        );

        $stored = $inspiration->store($appointment, $request->file('inspiration_photos', []), AppointmentInspirationPhoto::KIND_RESULT);

        if ($stored === 0) {
            return back()->withErrors(['inspiration_photos' => "That photo couldn't be read. Try a different one."]);
        }

        return back()->with('success', $stored === 1 ? 'After photo added.' : "{$stored} after photos added.");
    }

    public function staffDestroyResult(Appointment $appointment, AppointmentInspirationPhoto $photo)
    {
        $this->authorizeStaff($appointment, $photo);
        abort_unless($photo->isResult(), 404); // the client's own uploads aren't the business's to delete

        $photo->delete();

        return back()->with('success', 'After photo removed.');
    }

    public function staffSaveLook(Appointment $appointment, BookingNotificationService $notifications)
    {
        $this->authorizeStaff($appointment);

        if ($appointment->lookWasRemovedByClient()) {
            return back()->withErrors(['look' => ($appointment->customer?->name ?? 'The client') . ' removed this look, so it can\'t be saved again.']);
        }
        if (! $appointment->lookCanBeRecorded()) {
            return back()->withErrors(['look' => 'A look can be saved once the appointment has happened.']);
        }
        // Only the business's own after photos make a look; a client's Pinterest
        // screenshot alone shouldn't be kept 2 years as "your look".
        if (! $appointment->resultPhotos()->exists()) {
            return back()->withErrors(['look' => 'Add an after photo first, so there is a look to save.']);
        }

        $appointment->update(['look_saved_at' => now()]);
        $notifications->notifyLookSaved($appointment);

        return back()->with('success', 'Saved to ' . ($appointment->customer?->name ?? 'the client') . "'s looks. We've let them know.");
    }

    public function staffForgetLook(Appointment $appointment)
    {
        $this->authorizeStaff($appointment);

        $appointment->update(['look_saved_at' => null]);

        return back()->with('success', 'Saved look removed.');
    }

    public function staffShow(Appointment $appointment, AppointmentInspirationPhoto $photo, string $size)
    {
        $this->authorizeStaff($appointment, $photo);

        return $this->stream($photo, $size);
    }
}
