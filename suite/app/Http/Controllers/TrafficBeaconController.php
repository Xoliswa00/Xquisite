<?php

namespace App\Http\Controllers;

use App\Http\Middleware\TrackPageView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Receives scroll depth, time on page and clicks from the tracking script. Always answers 204. */
class TrafficBeaconController extends Controller
{
    private const MAX_CLICKS_PER_PAGE_VIEW = 300;

    public function store(Request $request)
    {
        $id = (int) $request->input('pv');

        if (! hash_equals(TrackPageView::token($id), (string) $request->input('t'))) {
            return response()->noContent();
        }

        $view = DB::table('page_views')->where('id', $id)->where('created_at', '>=', now()->subDay())->first();
        if (! $view) {
            return response()->noContent();
        }

        DB::table('page_views')->where('id', $id)->update([
            'max_scroll' => max((int) $view->max_scroll, min(100, max(0, (int) $request->input('scroll')))),
            'duration_seconds' => max((int) $view->duration_seconds, min(3600, max(0, (int) $request->input('seconds')))),
        ]);

        $clicks = json_decode((string) $request->input('clicks', '[]'), true);
        if (is_array($clicks)) {
            $room = self::MAX_CLICKS_PER_PAGE_VIEW - DB::table('click_events')->where('page_view_id', $id)->count();
            $rows = [];

            foreach (array_slice($clicks, 0, max(0, min(30, $room))) as $c) {
                if (! is_array($c) || ! isset($c['x'], $c['y'])) {
                    continue;
                }
                $rows[] = [
                    'page_view_id' => $id,
                    'path' => $view->path,
                    'x_pct' => round(min(100, max(0, (float) $c['x'])), 2),
                    'y_px' => min(100000, max(0, (int) $c['y'])),
                    'viewport' => ($c['v'] ?? '') === 'mobile' ? 'mobile' : 'desktop',
                    'kind' => in_array($c['k'] ?? '', ['link', 'button'], true) ? $c['k'] : 'other',
                    'label' => isset($c['l']) && $c['l'] !== '' ? mb_substr(trim((string) $c['l']), 0, 80) : null,
                    'href' => isset($c['h']) && $c['h'] !== '' ? mb_substr((string) $c['h'], 0, 255) : null,
                    'created_at' => now(),
                ];
            }

            if ($rows) {
                DB::table('click_events')->insert($rows);
            }
        }

        return response()->noContent();
    }
}
