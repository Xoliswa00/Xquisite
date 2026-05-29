<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SystemLog;
use Illuminate\Http\Request;

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

        if ($request->filled('search')) {
            $query->where('message', 'like', '%' . $request->search . '%');
        }

        $logs       = $query->paginate(30)->withQueryString();
        $unresolved = SystemLog::unresolvedCriticalCount();

        return view('logs.index', compact('logs', 'unresolved'));
    }

    public function show(SystemLog $log)
    {
        return view('logs.show', compact('log'));
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

        $logs = $query->paginate(30)->withQueryString();

        return view('logs.audit', compact('logs'));
    }
}
