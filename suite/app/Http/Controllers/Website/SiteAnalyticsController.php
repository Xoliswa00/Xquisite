<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SiteAnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);

        $activeTemplate = $tenant->activeTemplate?->template;

        $since30 = now()->subDays(29)->startOfDay();

        $totalVisits = $tenant->siteVisits()->count();
        $visits30 = $tenant->siteVisits()->where('visited_at', '>=', $since30)->count();
        $uniqueVisitors30 = $tenant->siteVisits()->where('visited_at', '>=', $since30)->distinct('visitor_hash')->count('visitor_hash');
        $visitsToday = $tenant->siteVisits()->whereDate('visited_at', today())->count();

        $dailyCounts = $tenant->siteVisits()
            ->where('visited_at', '>=', $since30)
            ->selectRaw('date(visited_at) as day, count(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day');

        $series = collect(range(0, 29))->map(function ($daysAgo) use ($dailyCounts) {
            $date = now()->subDays(29 - $daysAgo)->toDateString();
            return ['date' => $date, 'count' => (int) ($dailyCounts[$date] ?? 0)];
        })->values();

        $topReferrers = $tenant->siteVisits()
            ->where('visited_at', '>=', $since30)
            ->whereNotNull('referrer')
            ->select('referrer', DB::raw('count(*) as count'))
            ->groupBy('referrer')
            ->orderByDesc('count')
            ->limit(8)
            ->get()
            ->map(fn ($row) => ['label' => parse_url($row->referrer, PHP_URL_HOST) ?? $row->referrer, 'count' => $row->count]);

        $directVisits = $tenant->siteVisits()->where('visited_at', '>=', $since30)->whereNull('referrer')->count();
        if ($directVisits > 0) {
            $topReferrers->prepend(['label' => 'Direct / no referrer', 'count' => $directVisits]);
        }
        $topReferrers = $topReferrers->sortByDesc('count')->values()->take(8);

        return view('website.analytics.index', compact(
            'tenant', 'activeTemplate', 'totalVisits', 'visits30', 'uniqueVisitors30', 'visitsToday', 'series', 'topReferrers'
        ));
    }
}
