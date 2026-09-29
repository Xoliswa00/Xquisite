<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Records one anonymous page view per real visitor page load and adds the small
 * tracking script that reports scrolling and clicks.
 *
 * Privacy: no cookie, no IP address, no user id. The visitor id is a one-way hash of
 * IP + browser + today's date, so it cannot be traced to a person or followed across
 * days. "Do Not Track" and Global Privacy Control are honoured. The URL pattern is
 * stored (customers/{customer}), never the actual id or any token in the link.
 */
class TrackPageView
{
    private const BOTS = '/bot|crawl|spider|slurp|preview|monitor|uptime|curl|wget|python|java\/|go-http|headless|lighthouse|facebookexternalhit|whatsapp|telegram|discord|skype|pingdom|gtmetrix/i';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            if ($this->shouldTrack($request, $response)) {
                $response = $this->track($request, $response);
            }
        } catch (\Throwable $e) {
            // Analytics must never take a page down.
            logger()->warning('Page view tracking failed: ' . $e->getMessage());
        }

        return $response;
    }

    /** Proves a beacon really belongs to the page view it names. */
    public static function token(int $pageViewId): string
    {
        return substr(hash_hmac('sha256', 'pv|' . $pageViewId, config('app.key')), 0, 16);
    }

    private function shouldTrack(Request $request, Response $response): bool
    {
        if (! config('analytics.enabled') || ! $request->isMethod('GET') || $request->ajax() || $request->expectsJson()) {
            return false;
        }
        if ($response->getStatusCode() !== 200 || $response instanceof StreamedResponse || $response instanceof BinaryFileResponse) {
            return false;
        }
        if (! str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            return false;
        }
        if ($request->header('DNT') === '1' || $request->header('Sec-GPC') === '1' || $request->query('xq_no_track')) {
            return false;
        }
        if (in_array(strtolower((string) ($request->header('Sec-Purpose') ?: $request->header('Purpose'))), ['prefetch', 'prefetch;prerender'], true)) {
            return false;
        }

        $ua = (string) $request->userAgent();
        if ($ua === '' || preg_match(self::BOTS, $ua)) {
            return false;
        }

        $route = $request->route();
        if (! $route) {
            return false;
        }
        $first = explode('/', trim($route->uri(), '/'))[0];
        if (in_array($first, config('analytics.excluded_prefixes'), true)) {
            return false;
        }

        // Our own team browsing the platform would inflate the numbers.
        $user = $request->user();
        if ($user && ! $user->tenant_id) {
            return false;
        }

        return true;
    }

    private function track(Request $request, Response $response): Response
    {
        $ua = (string) $request->userAgent();
        $uri = trim($request->route()->uri(), '/');

        $referrerHost = parse_url((string) $request->headers->get('referer'), PHP_URL_HOST) ?: null;
        if ($referrerHost === $request->getHost()) {
            $referrerHost = null;
        }

        $source = $request->query('src') ?: $request->query('utm_source');

        $id = DB::table('page_views')->insertGetId([
            'visitor_hash' => substr(hash('sha256', config('app.key') . '|' . now()->toDateString() . '|' . $request->ip() . '|' . $ua), 0, 16),
            'path' => mb_substr('/' . $uri, 0, 255),
            'source' => $source ? mb_substr(strtolower((string) $source), 0, 60) : null,
            'campaign' => $request->query('utm_campaign') ? mb_substr((string) $request->query('utm_campaign'), 0, 100) : null,
            'referrer_host' => $referrerHost ? mb_substr($referrerHost, 0, 120) : null,
            'device' => match (true) {
                (bool) preg_match('/iPad|Tablet/i', $ua) => 'tablet',
                (bool) preg_match('/Mobi|Android|iPhone/i', $ua) => 'mobile',
                default => 'desktop',
            },
            'is_authenticated' => $request->user() !== null,
            'created_at' => now(),
        ]);

        $content = (string) $response->getContent();
        $pos = strripos($content, '</body>');
        if ($pos === false) {
            return $response;
        }

        $tag = '<script src="' . asset('js/xq-track.js') . '" data-pv="' . $id . '" data-t="' . self::token($id) . '" data-endpoint="' . route('traffic.beacon') . '" defer></script>';
        $response->setContent(substr($content, 0, $pos) . $tag . substr($content, $pos));

        return $response;
    }
}
