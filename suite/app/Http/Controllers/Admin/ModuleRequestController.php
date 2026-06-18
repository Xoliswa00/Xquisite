<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModuleRequest;
use App\Notifications\ModuleRequestStatusChanged;
use Illuminate\Http\Request;

class ModuleRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = ModuleRequest::with(['tenant', 'user', 'reviewedBy'])
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderByDesc('requested_at')
            ->paginate(20);

        return view('admin.module_requests.index', compact('requests'));
    }

    public function approve(Request $request, ModuleRequest $moduleRequest)
    {
        if ($moduleRequest->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be reviewed.');
        }

        $request->validate([
            'price_override' => 'nullable|numeric|min:0',
            'review_notes'   => 'nullable|string|max:1000',
        ]);

        $moduleRequest->update([
            'status'        => 'approved',
            'price_override'=> $request->filled('price_override') ? (float) $request->price_override : $moduleRequest->price_override,
            'review_notes'  => $request->review_notes,
            'reviewed_by'   => auth()->id(),
            'reviewed_at'   => now(),
        ]);

        $moduleRequest->tenant->activateModule(
            $moduleRequest->module,
            auth()->id(),
            $moduleRequest->price_override
        );

        $moduleRequest->user->notify(new ModuleRequestStatusChanged($moduleRequest));

        return back()->with('success', 'Module request approved and activated successfully.');
    }

    public function reject(Request $request, ModuleRequest $moduleRequest)
    {
        if ($moduleRequest->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be reviewed.');
        }

        $request->validate([
            'review_notes' => 'nullable|string|max:1000',
        ]);

        $moduleRequest->update([
            'status'       => 'rejected',
            'review_notes' => $request->review_notes,
            'reviewed_by'  => auth()->id(),
            'reviewed_at'  => now(),
        ]);

        $moduleRequest->user->notify(new ModuleRequestStatusChanged($moduleRequest));

        return back()->with('success', 'Module request rejected successfully.');
    }
}
