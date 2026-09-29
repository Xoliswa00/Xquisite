<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FoundingTwentyApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrafficController extends Controller
{
    /** The Founding 20 journey, page by page. */
    private const FOUNDING_20_STEPS = [
        '/founding-20' => 'Read the programme page',
        '/founding-20/apply' => 'Opened the application',
        '/founding-20/apply/questions' => 'Reached the questions',
        '/founding-20/thanks' => 'Finished the application',
    ];

    public function index(Request $request)
    {
        $days = in_array((int) $request->query('days'), [7, 30, 90], true) ? (int) $request->query('days') : 7;
        $scope = in_array($request->query('scope'), ['public', 'app', 'all'], true) ? $request->query('scope') : 'public';

        $since = now()->subDays($days - 1)->startOfDay();
        $previousSince = $since->copy()->subDays($days);

        $views = fn ($from = null, $to = null) => DB::table('page_views')
            ->where('created_at', '>=', $from ?? $since)
            ->when($to, fn ($q) => $q->where('created_at', '<', $to))
            ->when($scope === 'public', fn ($q) => $q->where('is_authenticated', false))
            ->when($scope === 'app', fn ($q) => $q->where('is_authenticated', true));

        // Day by day. Visitors are counted per day (the visitor code changes daily), then added up.
        $byDay = $views()->selectRaw('DATE(created_at) as d, COUNT(*) as views, COUNT(DISTINCT visitor_hash) as visitors')
            ->groupBy('d')->orderBy('d')->get()->keyBy('d');
        $series = collect(range($days - 1, 0))->map(function ($ago) use ($byDay) {
            $date = now()->subDays($ago)->toDateString();

            return ['date' => $date, 'views' => (int) ($byDay[$date]->views ?? 0), 'visitors' => (int) ($byDay[$date]->visitors ?? 0)];
        });

        $totals = [
            'views' => $series->sum('views'),
            'visitors' => $series->sum('visitors'),
            'avg_seconds' => round((float) $views()->whereNotNull('duration_seconds')->where('duration_seconds', '>', 0)->avg('duration_seconds')),
            'avg_scroll' => round((float) $views()->whereNotNull('max_scroll')->where('max_scroll', '>', 0)->avg('max_scroll')),
        ];

        $topPages = $views()->selectRaw('path, COUNT(*) as views, COUNT(DISTINCT visitor_hash) as visitors, AVG(NULLIF(max_scroll, 0)) as scroll, AVG(NULLIF(duration_seconds, 0)) as seconds')
            ->groupBy('path')->orderByDesc('views')->limit(15)->get();

        $sources = $views()->selectRaw("COALESCE(source, referrer_host, 'Direct') as origin, COUNT(*) as views, COUNT(DISTINCT visitor_hash) as visitors")
            ->groupBy('origin')->orderByDesc('views')->limit(10)->get();

        $devices = $views()->selectRaw('device, COUNT(*) as views')->groupBy('device')->orderByDesc('views')->get();

        $topClicks = DB::table('click_events')
            ->join('page_views', 'page_views.id', '=', 'click_events.page_view_id')
            ->where('click_events.created_at', '>=', $since)
            ->when($scope === 'public', fn ($q) => $q->where('page_views.is_authenticated', false))
            ->when($scope === 'app', fn ($q) => $q->where('page_views.is_authenticated', true))
            ->where(fn ($q) => $q->whereNotNull('click_events.label')->orWhereNotNull('click_events.href'))
            ->where('click_events.kind', '!=', 'other')
            ->selectRaw('click_events.path as path, click_events.label as label, click_events.href as href, COUNT(*) as clicks')
            ->groupBy('click_events.path', 'click_events.label', 'click_events.href')
            ->orderByDesc('clicks')->limit(15)->get();

        return view('admin.traffic.index', [
            'days' => $days,
            'scope' => $scope,
            'series' => $series,
            'totals' => $totals,
            'topPages' => $topPages,
            'sources' => $sources,
            'devices' => $devices,
            'topClicks' => $topClicks,
            'founding20' => $this->founding20($since, $previousSince),
        ]);
    }

    /** Is the Founding 20 getting attention, and where do people drop off? Always visitors from the public site. */
    private function founding20($since, $previousSince): array
    {
        $visitorsAt = fn ($path, $from, $to = null) => (int) DB::table('page_views')
            ->where('path', $path)->where('is_authenticated', false)->where('created_at', '>=', $from)
            ->when($to, fn ($q) => $q->where('created_at', '<', $to))
            ->selectRaw('COUNT(DISTINCT visitor_hash) as n')->value('n');

        $steps = [];
        $previous = null;
        foreach (self::FOUNDING_20_STEPS as $path => $label) {
            $count = $visitorsAt($path, $since);
            $steps[] = ['label' => $label, 'path' => $path, 'visitors' => $count, 'of_previous' => $previous ? (int) round($count / $previous * 100) : null];
            $previous = $count;
        }

        $now = $steps[0]['visitors'];
        $before = $visitorsAt('/founding-20', $previousSince, $since);

        $ctaClicks = (int) DB::table('click_events')->where('path', '/founding-20')->where('created_at', '>=', $since)
            ->where('href', 'like', '%/founding-20/apply%')->count();

        return [
            'steps' => $steps,
            'visitors' => $now,
            'previous_visitors' => $before,
            'change' => $before > 0 ? (int) round(($now - $before) / $before * 100) : null,
            'cta_clicks' => $ctaClicks,
            'started' => FoundingTwentyApplication::where('created_at', '>=', $since)->count(),
            'submitted' => FoundingTwentyApplication::where('submitted_at', '>=', $since)->count(),
        ];
    }

    public function heatmap(Request $request)
    {
        $days = in_array((int) $request->query('days'), [7, 30, 90], true) ? (int) $request->query('days') : 30;
        $device = $request->query('device') === 'desktop' ? 'desktop' : 'mobile';
        $since = now()->subDays($days - 1)->startOfDay();

        // Only pages with a fixed address can be shown behind the heat map.
        $pages = DB::table('click_events')->where('created_at', '>=', $since)->where('path', 'not like', '%{%')
            ->selectRaw('path, COUNT(*) as clicks')->groupBy('path')->orderByDesc('clicks')->get();

        $path = $request->query('path');
        if (! $pages->contains('path', $path)) {
            $path = $pages->contains('path', '/founding-20') ? '/founding-20' : $pages->first()->path ?? null;
        }

        $points = $path ? DB::table('click_events')->where('path', $path)->where('viewport', $device)->where('created_at', '>=', $since)
            ->orderByDesc('id')->limit(5000)->get(['x_pct', 'y_px']) : collect();

        $elements = $path ? DB::table('click_events')->where('path', $path)->where('viewport', $device)->where('created_at', '>=', $since)
            ->where('kind', '!=', 'other')->where(fn ($q) => $q->whereNotNull('label')->orWhereNotNull('href'))
            ->selectRaw('label, href, COUNT(*) as clicks')->groupBy('label', 'href')->orderByDesc('clicks')->limit(12)->get() : collect();

        $reach = $path ? DB::table('page_views')->where('path', $path)->where('created_at', '>=', $since)->whereNotNull('max_scroll')
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN max_scroll >= 25 THEN 1 ELSE 0 END) as q1, SUM(CASE WHEN max_scroll >= 50 THEN 1 ELSE 0 END) as q2, SUM(CASE WHEN max_scroll >= 75 THEN 1 ELSE 0 END) as q3, SUM(CASE WHEN max_scroll >= 95 THEN 1 ELSE 0 END) as q4')
            ->first() : null;

        return view('admin.traffic.heatmap', compact('days', 'device', 'pages', 'path', 'points', 'elements', 'reach'));
    }
}
