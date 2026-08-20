<?php

namespace App\Http\Controllers\ServiceDelivery;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Modules\ServiceDelivery\Models\Gig;
use App\Modules\ServiceDelivery\Models\GigTimeEntry;
use Illuminate\Http\Request;

class GigController extends Controller
{
    public function index(Request $request)
    {
        $query = Gig::with(['client', 'quotes'])->latest();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Kanban board — group every (non-cancelled, capped) gig by status rather than
        // paginating, so all columns are visible at once. Cancelled gigs are hidden by
        // default to keep the board focused on live work.
        $gigs = $query->where('status', '!=', 'cancelled')->limit(200)->get()->groupBy('status');

        $statuses = ['lead', 'quoted', 'in_progress', 'review', 'completed'];

        return view('service-delivery.gigs.index', compact('gigs', 'statuses'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();

        return view('service-delivery.gigs.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'       => 'required|exists:clients,id',
            'category'        => 'required|in:software_solutions,business_automation,data_intelligence,digital_solutions,other',
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'discovery_notes' => 'nullable|string',
            'deadline_date'   => 'nullable|date',
            'hourly_rate'     => 'nullable|numeric|min:0',
        ]);

        $validated['status'] = 'lead';

        $gig = Gig::create($validated);

        return redirect()->route('gigs.show', $gig)->with('success', 'Gig created — ready to scope and quote.');
    }

    public function show(Gig $gig)
    {
        $gig->load(['client', 'quotes' => fn ($q) => $q->latest(), 'timeEntries' => fn ($q) => $q->latest('logged_at')]);

        return view('service-delivery.gigs.show', compact('gig'));
    }

    public function edit(Gig $gig)
    {
        $clients = Client::orderBy('name')->get();

        return view('service-delivery.gigs.edit', compact('gig', 'clients'));
    }

    public function update(Request $request, Gig $gig)
    {
        $validated = $request->validate([
            'category'        => 'required|in:software_solutions,business_automation,data_intelligence,digital_solutions,other',
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'discovery_notes' => 'nullable|string',
            'deadline_date'   => 'nullable|date',
            'hourly_rate'     => 'nullable|numeric|min:0',
            'notes'           => 'nullable|string',
        ]);

        $gig->update($validated);

        return redirect()->route('gigs.show', $gig)->with('success', 'Gig updated.');
    }

    public function updateStatus(Request $request, Gig $gig)
    {
        $validated = $request->validate([
            'status' => 'required|in:lead,quoted,in_progress,review,completed,cancelled',
        ]);

        if ($validated['status'] === 'in_progress' && !$gig->started_at) {
            $validated['started_at'] = now();
        }

        if ($validated['status'] === 'completed' && !$gig->completed_at) {
            $validated['completed_at'] = now();
        }

        $gig->update($validated);

        return redirect()->route('gigs.show', $gig)->with('success', 'Status updated.');
    }

    public function destroy(Gig $gig)
    {
        abort_unless($gig->status === 'lead', 422, 'Only leads that haven\'t been quoted can be deleted.');

        $gig->delete();

        return redirect()->route('gigs.index')->with('success', 'Gig deleted.');
    }

    public function logTime(Request $request, Gig $gig)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'minutes'     => 'required|integer|min:1',
            'logged_at'   => 'required|date',
        ]);

        GigTimeEntry::create(array_merge($validated, [
            'gig_id'    => $gig->id,
            'logged_by' => auth()->id(),
        ]));

        return redirect()->route('gigs.show', $gig)->with('success', 'Time logged.');
    }
}
