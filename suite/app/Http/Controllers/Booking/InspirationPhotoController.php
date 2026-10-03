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

    /** Customer removes a saved look; its photos go back to the normal 90-day retention. */
    public function customerForgetLook(string $slug, Appointment $appointment)
    {
        $this->authorizeCustomer($this->resolveTenant($slug), $appointment);

        $appointment->update(['look_saved_at' => null]);

        return back()->with('success', 'Saved look removed.');
    }

    /** "Book this look again": remember which look to copy, then start a booking for the same services. */
    public function customerRebookLook(string $slug, Appointment $appointment)
    {
        $this->authorizeCustomer($this->resolveTenant($slug), $appointment);
        abort_unless($appointment->isLookSaved(), 404);

        $serviceIds = $appointment->services()->where('services.is_active', true)->pluck('services.id')->all();
        if ($serviceIds === []) {
            return redirect()->route('book.index', $slug)
                ->withErrors(['look' => "Those services aren't on offer any more. Pick what you'd like and we'll still attach your look."]);
        }

        session(['rebook_look' => $appointment->id]);

        return redirect()->route('book.service', ['slug' => $slug, 'service_ids' => $serviceIds]);
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

    public function staffSaveLook(Appointment $appointment)
    {
        $this->authorizeStaff($appointment);

        if (! $appointment->lookPhotos()->exists() && ! $appointment->inspiration_notes) {
            return back()->withErrors(['look' => 'Add an after photo first, so there is a look to save.']);
        }

        $appointment->update(['look_saved_at' => now()]);

        return back()->with('success', 'Saved to ' . ($appointment->customer?->name ?? 'the client') . "'s looks.");
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
