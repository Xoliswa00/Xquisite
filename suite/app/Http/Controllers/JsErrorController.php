<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JsErrorController extends Controller
{
    private function corsHeaders(): array
    {
        return [
            'Access-Control-Allow-Origin'  => '*',
            'Access-Control-Allow-Methods' => 'POST, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type',
            'Access-Control-Max-Age'       => '86400',
        ];
    }

    public function preflight()
    {
        return response()->noContent(204, $this->corsHeaders());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'message' => 'required|string|max:500',
            'source'  => 'nullable|string|max:200',
            'line'    => 'nullable|integer',
            'col'     => 'nullable|integer',
            'url'     => 'nullable|string|max:500',
            'stack'   => 'nullable|string|max:2000',
            'project' => 'nullable|string|max:100',
        ]);

        $path    = parse_url($data['url'] ?? '', PHP_URL_PATH) ?? '';
        $project = $data['project'] ?? null;

        $source = match (true) {
            $project !== null                   => $project,
            str_contains($path, '/book/')       => 'booking-portal',
            str_contains($path, '/admin/')      => 'admin',
            str_contains($path, '/portal/')     => 'client-portal',
            str_contains($path, '/shop/')       => 'shop',
            default                             => 'suite',
        };

        try {
            $sessionId = session()->getId();
        } catch (\Throwable) {
            $sessionId = null;
        }

        DB::table('system_logs')->insert([
            'level'      => 'WARNING',
            'message'    => '[JS] ' . $data['message'],
            'context'    => json_encode([
                'js_source'  => $data['source'] ?? null,
                'line'       => $data['line'] ?? null,
                'col'        => $data['col'] ?? null,
                'stack'      => $data['stack'] ?? null,
                'page_url'   => $data['url'] ?? null,
                'referrer'   => $request->header('Referer'),
                'user_agent' => $request->header('User-Agent'),
                'session_id' => $sessionId,
                'location'   => \App\Support\IpLocation::get($request->ip()),
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

        return response()->noContent(204, $this->corsHeaders());
    }
}
