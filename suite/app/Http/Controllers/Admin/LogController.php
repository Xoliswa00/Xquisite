<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Modules\Core\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = SystemLog::latest();

        if ($request->filled('level')) {
            $query->where('level', strtoupper($request->level));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('search')) {
            $query->where('message', 'like', '%' . $request->search . '%');
        }

        $logs       = $query->paginate(50)->withQueryString();
        $unresolved = SystemLog::unresolvedCriticalCount();
        $levels     = SystemLog::distinct()->orderBy('level')->pluck('level')->filter()->values();
        $sources    = SystemLog::distinct()->orderBy('source')->pluck('source')->filter()->values();
        $statuses   = ['new', 'acknowledged', 'in_progress', 'resolved'];

        return view('admin.logs.index', compact('logs', 'unresolved', 'levels', 'sources', 'statuses'));
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array',
            'ids.*'  => 'integer',
            'action' => 'required|in:acknowledged,in_progress,resolved',
        ]);

        $note = $request->action === 'resolved' ? ($request->note ?? 'Bulk resolved') : null;

        SystemLog::whereIn('id', $request->ids)->each(function ($log) use ($request, $note) {
            if ($request->action === 'resolved') {
                $log->resolve(auth()->id(), $note);
            } else {
                $log->update(['status' => $request->action]);
            }
        });

        return back()->with('success', count($request->ids) . ' log(s) updated.');
    }

    public function resolveAll(Request $request)
    {
        $query = SystemLog::where('status', '!=', 'resolved');

        if ($request->filled('level'))  $query->where('level', strtoupper($request->level));
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('source')) $query->where('source', $request->source);
        if ($request->filled('search')) $query->where('message', 'like', '%' . $request->search . '%');

        $count = $query->count();
        $query->update([
            'status'          => 'resolved',
            'resolved_by'     => auth()->id(),
            'resolved_at'     => now(),
            'resolution_note' => 'Bulk resolved all',
            'updated_at'      => now(),
        ]);

        return back()->with('success', "{$count} log(s) resolved.");
    }

    public function show(SystemLog $log)
    {
        return view('admin.logs.show', compact('log'));
    }

    public function updateStatus(Request $request, SystemLog $log)
    {
        $validated = $request->validate([
            'status'          => 'required|in:acknowledged,in_progress,resolved',
            'resolution_note' => 'nullable|string|max:1000',
        ]);

        if ($validated['status'] === 'resolved') {
            $log->resolve(auth()->id(), $validated['resolution_note'] ?? null);
        } else {
            $log->update(['status' => $validated['status']]);
        }

        return back()->with('success', 'Log status updated.');
    }

    public function audit(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        $logs = $query->paginate(30)->withQueryString();

        return view('admin.logs.audit', compact('logs'));
    }

    public function combined(Request $request)
    {
        // Local suite logs
        $suiteLogs = SystemLog::latest()
            ->limit(50)
            ->get()
            ->map(fn ($l) => array_merge($l->toArray(), ['_source' => 'suite']));

        // Pull billing logs via bridge
        $billingLogs = collect();
        $apiKey      = config('billing.internal_key');
        $billingUrl  = config('billing.url');

        if ($apiKey && $billingUrl) {
            try {
                $response = Http::timeout(5)
                    ->withHeaders(['X-Internal-Key' => $apiKey])
                    ->get("{$billingUrl}/api/internal/logs");

                if ($response->successful()) {
                    $billingLogs = collect($response->json('logs', []))
                        ->map(fn ($l) => array_merge($l, ['_source' => 'billing']));
                }
            } catch (\Throwable) {
                // Billing unreachable — show suite logs only
            }
        }

        $all = $suiteLogs->concat($billingLogs)
            ->sortByDesc('created_at')
            ->values();

        return view('admin.logs.combined', compact('all'));
    }
}
