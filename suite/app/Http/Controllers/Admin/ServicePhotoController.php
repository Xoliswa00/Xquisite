<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhotoReport;
use App\Modules\Booking\Models\ServicePhoto;
use Illuminate\Http\Request;

class ServicePhotoController extends Controller
{
    /** Platform-wide review list. Reported photos first, then everything else. */
    public function index(Request $request)
    {
        $filter = in_array($request->get('filter'), ['reported', 'hidden', 'all'], true)
            ? $request->get('filter')
            : 'reported';

        // Platform view: ignore tenant scoping on the photo AND its eager-loaded
        // service/tenant, otherwise a super-admin only ever sees their own tenant's rows.
        $query = ServicePhoto::withoutGlobalScopes()
            ->with([
                'service' => fn($q) => $q->withoutGlobalScopes()->select('id', 'name', 'tenant_id')
                    ->with(['tenant' => fn($t) => $t->select('id', 'name', 'slug')]),
            ])
            ->withCount([
                'reports',
                'reports as open_reports_count' => fn($q) => $q->whereNull('reviewed_at'),
            ]);

        $query = match ($filter) {
            'hidden' => $query->whereNotNull('hidden_at'),
            'all'    => $query,
            default  => $query->whereHas('reports', fn($q) => $q->whereNull('reviewed_at')),
        };

        $photos = $query
            ->orderByDesc('open_reports_count')
            ->latest('service_photos.id')
            ->paginate(40)
            ->withQueryString();

        $openReports = PhotoReport::whereNull('reviewed_at')->count();

        return view('admin.service-photos.index', compact('photos', 'filter', 'openReports'));
    }

    public function toggleHidden(Request $request, int $photo)
    {
        // Bind manually so the tenant global scope doesn't 404 a cross-tenant photo.
        $photo = ServicePhoto::withoutGlobalScopes()->findOrFail($photo);

        if ($photo->isHidden()) {
            $photo->unhide();
            $message = 'Photo restored — visible on the booking page again.';
        } else {
            $photo->hide($request->input('reason', 'Hidden by platform moderation.'));
            // Clear any open reports against a photo we've now acted on.
            $photo->reports()->whereNull('reviewed_at')->update(['reviewed_at' => now()]);
            $message = 'Photo hidden from the public booking page.';
        }

        return back()->with('success', $message);
    }

    public function markReportReviewed(PhotoReport $report)
    {
        $report->update(['reviewed_at' => now()]);

        return back()->with('success', 'Report marked reviewed.');
    }
}
