<?php

namespace App\Http\Controllers;

use App\Models\Modules\Core\Models\SystemLog;
use App\Notifications\CriticalLogAlert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ingests batched application-error events pushed by external reporter apps
 * (Keystone/Loan, bx-eventos, jblack-mc/xquisite, …) and writes them into the
 * shared system_logs table so they show in the central log viewer
 * (/admin/logs?source=<slug>).
 *
 * Auth: EnsureMonitoredInstance middleware (bearer token → MonitoredInstance).
 * CSRF-exempt (see bootstrap/app.php). Structurally mirrors JsErrorController.
 */
class LogIngestController extends Controller
{
    /** Canonical PSR levels, stored uppercase to match DatabaseLogger + LogController's filter. */
    private const LEVELS = ['DEBUG', 'INFO', 'NOTICE', 'WARNING', 'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];

    private const ALERT_LEVELS = ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];

    private const MAX_CONTEXT_BYTES = 16000;

    private function corsHeaders(): array
    {
        return [
            'Access-Control-Allow-Origin'  => '*',
            'Access-Control-Allow-Methods' => 'POST, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
            'Access-Control-Max-Age'       => '86400',
        ];
    }

    public function preflight(): Response
    {
        return response()->noContent(204, $this->corsHeaders());
    }

    public function store(Request $request): JsonResponse
    {
        /** @var \App\Models\MonitoredInstance $instance */
        $instance = $request->attributes->get('monitored_instance');

        if (! $instance->slug) {
            return response()->json([
                'error'   => 'Not configured',
                'message' => 'This monitored instance has no slug set — cannot tag forwarded logs.',
            ], 422, $this->corsHeaders());
        }

        $data = $request->validate([
            'events'               => 'required|array|min:1|max:100',
            'events.*.level'       => 'required|string|max:20',
            'events.*.message'     => 'required|string|max:2000',
            'events.*.logged_at'   => 'required|date',
            'events.*.context'     => 'nullable|array',
            'events.*.file'        => 'nullable|string|max:1024',
            'events.*.line'        => 'nullable|integer',
            'events.*.url'         => 'nullable|string|max:1024',
            'events.*.fingerprint' => 'nullable|string|max:128',
            'events.*.request_id'  => 'nullable|string|max:128',
        ]);

        $slug = $instance->slug;
        $now  = now();
        $rows = [];

        foreach ($data['events'] as $event) {
            $level = strtoupper(trim($event['level']));
            if (! in_array($level, self::LEVELS, true)) {
                $level = 'ERROR';
            }

            $message = Str::limit((string) $event['message'], 2000, '');
            $file    = $event['file'] ?? ($event['context']['file'] ?? null);
            $line    = $event['line'] ?? ($event['context']['line'] ?? null);
            $url     = $event['url'] ?? null;

            $fingerprint = $event['fingerprint']
                ?? implode('|', [$level, $message, $file, $line, $event['logged_at']]);

            $rows[] = [
                'level'      => $level,
                'message'    => $message,
                'context'    => $this->encodeContext($event, $slug),
                'file'       => $file ? Str::limit((string) $file, 1024, '') : null,
                'line'       => is_numeric($line) ? (int) $line : null,
                'user_id'    => null, // reporter user ids are meaningless in this DB
                'request_id' => $event['request_id'] ?? null,
                'ip_address' => $request->ip(),
                'url'        => $url ? Str::limit((string) $url, 1024, '') : null,
                'status'     => 'new',
                'source'     => $slug,
                'dedup_key'  => sha1($instance->id.'|'.$fingerprint),
                // Hub ingest time, NOT reporter time — a skewed reporter clock
                // must not pin rows to the top of the viewer's latest() order.
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        [$accepted, $duplicates, $topLogId] = $this->persist($slug, $rows);

        if ($topLogId !== null && Cache::add("ingest-alert:{$slug}", true, now()->addMinutes(15))) {
            $this->alertAdmin($topLogId);
        }

        return response()->json([
            'accepted'   => $accepted,
            'duplicates' => $duplicates,
            'instance'   => $slug,
        ], 200, $this->corsHeaders());
    }

    /**
     * Insert the non-duplicate rows in one transaction.
     *
     * @return array{0:int,1:int,2:?int} [accepted, duplicates, highest-severity inserted log id or null]
     */
    private function persist(string $slug, array $rows): array
    {
        $keys = array_column($rows, 'dedup_key');

        return DB::transaction(function () use ($slug, $rows, $keys) {
            $existing = DB::table('system_logs')
                ->where('source', $slug)
                ->whereIn('dedup_key', $keys)
                ->pluck('dedup_key')
                ->all();

            $existing = array_flip($existing);
            $fresh    = array_values(array_filter($rows, fn ($r) => ! isset($existing[$r['dedup_key']])));

            if ($fresh === []) {
                return [0, count($rows), null];
            }

            DB::table('system_logs')->insert($fresh);

            // Highest-severity freshly-inserted row, for the (cooled-down) alert.
            // Rank in PHP so this stays portable (no MySQL FIELD()).
            $topKey  = null;
            $topRank = -1;
            foreach ($fresh as $row) {
                $rank = array_search($row['level'], self::ALERT_LEVELS, true);
                if ($rank !== false && $rank > $topRank) {
                    $topRank = $rank;
                    $topKey  = $row['dedup_key'];
                }
            }

            $topLogId = $topKey === null ? null : DB::table('system_logs')
                ->where('source', $slug)
                ->where('dedup_key', $topKey)
                ->value('id');

            return [count($fresh), count($rows) - count($fresh), $topLogId];
        });
    }

    /**
     * Build the stored context JSON string, keeping it under MAX_CONTEXT_BYTES.
     * Drops the (potentially large) trace first, then falls back to a stub.
     */
    private function encodeContext(array $event, string $slug): string
    {
        $context = (array) ($event['context'] ?? []);
        $context['_forwarded_from']      = $slug;
        $context['_reporter_logged_at']  = $event['logged_at'];
        $context['_reporter_request_id'] = $event['request_id'] ?? null;

        $json = json_encode($context);
        if ($json !== false && strlen($json) <= self::MAX_CONTEXT_BYTES) {
            return $json;
        }

        unset($context['trace']);
        $context['_truncated'] = 'trace dropped — exceeded '.self::MAX_CONTEXT_BYTES.' bytes';
        $json = json_encode($context);
        if ($json !== false && strlen($json) <= self::MAX_CONTEXT_BYTES) {
            return $json;
        }

        return json_encode([
            '_forwarded_from' => $slug,
            '_truncated'      => 'context too large — discarded',
        ]);
    }

    /**
     * Mirror of App\Logging\DatabaseLogger's alertAdmin(): one anonymous mail
     * notification to the configured from-address. Cooldown is handled by the
     * caller so a 100-row batch can't send 100 emails.
     */
    private function alertAdmin(int $logId): void
    {
        try {
            $adminEmail = config('mail.from.address');
            if (! $adminEmail) {
                return;
            }

            $log = SystemLog::find($logId);
            if (! $log) {
                return;
            }

            Notification::route('mail', $adminEmail)->notify(new CriticalLogAlert($log));
        } catch (\Throwable) {
            // Notification failure must never break ingest.
        }
    }
}
