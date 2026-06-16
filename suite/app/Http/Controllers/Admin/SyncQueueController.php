<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SyncQueue;
use Illuminate\Http\Request;

class SyncQueueController extends Controller
{
    public function index(Request $request)
    {
        $query = SyncQueue::with('tenant')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $items = $query->paginate(30)->withQueryString();

        $counts = [
            'pending'   => SyncQueue::where('status', 'pending')->count(),
            'retrying'  => SyncQueue::where('status', 'retrying')->count(),
            'completed' => SyncQueue::where('status', 'completed')->count(),
            'abandoned' => SyncQueue::where('status', 'abandoned')->count(),
        ];

        return view('admin.sync.index', compact('items', 'counts'));
    }

    public function retryOne(SyncQueue $syncQueue)
    {
        $syncQueue->retryNow();
        return back()->with('success', "Item #{$syncQueue->id} queued for immediate retry.");
    }

    public function retryAll()
    {
        $count = SyncQueue::whereIn('status', ['pending', 'abandoned'])->count();

        SyncQueue::whereIn('status', ['pending', 'abandoned'])->update([
            'status'        => 'pending',
            'next_retry_at' => now(),
            'last_error'    => null,
        ]);

        return back()->with('success', "{$count} item(s) reset for retry. The scheduler will process them within 5 minutes.");
    }

    public function dismiss(SyncQueue $syncQueue)
    {
        $syncQueue->update(['status' => 'abandoned']);
        return back()->with('success', "Item #{$syncQueue->id} dismissed.");
    }
}
