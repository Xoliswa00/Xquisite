<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JsErrorController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'message'  => 'required|string|max:500',
            'source'   => 'nullable|string|max:200',
            'line'     => 'nullable|integer',
            'col'      => 'nullable|integer',
            'url'      => 'nullable|string|max:500',
            'stack'    => 'nullable|string|max:2000',
        ]);

        $path       = parse_url($data['url'] ?? '', PHP_URL_PATH) ?? '';
        $source     = match (true) {
            str_contains($path, '/book/')   => 'booking-portal',
            str_contains($path, '/admin/')  => 'admin',
            str_contains($path, '/portal/') => 'client-portal',
            str_contains($path, '/shop/')   => 'shop',
            default                         => 'suite',
        };

        DB::table('system_logs')->insert([
            'level'      => 'WARNING',
            'message'    => '[JS] ' . $data['message'],
            'context'    => json_encode([
                'js_source' => $data['source'] ?? null,
                'line'      => $data['line'] ?? null,
                'col'       => $data['col'] ?? null,
                'stack'     => $data['stack'] ?? null,
                'page_url'  => $data['url'] ?? null,
            ]),
            'request_id' => app()->bound('request_id') ? app('request_id') : null,
            'user_id'    => auth()->id(),
            'ip_address' => $request->ip(),
            'url'        => $data['url'] ?? $request->fullUrl(),
            'status'     => 'new',
            'source'     => $source,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->noContent();
    }
}
