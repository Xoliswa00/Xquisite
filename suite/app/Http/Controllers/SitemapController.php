<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $static = [
            ['url' => url('/'),         'priority' => '1.0', 'changefreq' => 'weekly'],
            ['url' => url('/about'),    'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => url('/register'), 'priority' => '0.9', 'changefreq' => 'monthly'],
            ['url' => url('/login'),    'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => url('/terms'),    'priority' => '0.4', 'changefreq' => 'yearly'],
            ['url' => url('/privacy'),  'priority' => '0.4', 'changefreq' => 'yearly'],
        ];

        $tenants = Tenant::where('is_active', true)
            ->whereNotNull('slug')
            ->with('activeModules')
            ->get();

        // Public booking portals — one per active tenant
        $booking = $tenants->map(fn($t) => [
            'url'        => url("/book/{$t->slug}"),
            'priority'   => '0.6',
            'changefreq' => 'weekly',
        ])->all();

        // Public storefronts — only tenants with ecommerce module active
        $shops = $tenants
            ->filter(fn($t) => $t->hasModule('ecommerce'))
            ->map(fn($t) => [
                'url'        => url("/shop/{$t->slug}"),
                'priority'   => '0.6',
                'changefreq' => 'weekly',
            ])->all();

        $urls = array_merge($static, $booking, $shops);

        $today = now()->toDateString();

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $entry) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($entry['url']) . "</loc>\n";
            $xml .= "    <lastmod>{$today}</lastmod>\n";
            $xml .= "    <changefreq>{$entry['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$entry['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
