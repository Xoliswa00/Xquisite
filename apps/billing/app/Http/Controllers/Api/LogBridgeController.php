<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use Illuminate\Http\Request;

class LogBridgeController extends Controller
{
    public function index(Request $request)
    {
        $query = SystemLog::latest()->limit(50);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('level')) {
            $query->where('level', strtoupper($request->level));
        }

        $logs = $query->get([
            'id', 'level', 'message', 'url', 'ip_address',
            'status', 'source', 'created_at', 'resolved_at',
        ]);

        return response()->json([
            'logs'            => $logs,
            'unresolved_count' => SystemLog::unresolvedCriticalCount(),
        ]);
    }
}
