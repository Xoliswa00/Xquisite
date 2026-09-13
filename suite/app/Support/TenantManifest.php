<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

/**
 * Builds a per-tenant web app manifest — shared by every portal (booking,
 * shop, renter, contractor) so each one's "Add to Home Screen" icon shows
 * that business's own name, not a generic platform label.
 *
 * This matters more here than on a typical single-tenant site: one person
 * can plausibly have several of these installed at once — their landlord's
 * renter portal, a contractor job they're doing, a salon they book with —
 * and without per-tenant naming every one of those home-screen icons would
 * be an indistinguishable generic "App" icon.
 */
class TenantManifest
{
    public static function build(Tenant $tenant, string $startUrl): array
    {
        $icons = [];

        if ($tenant->logo_url) {
            // Sizes declared optimistically — browsers scale a near-enough
            // source in practice, and this is still strictly better than no
            // branded icon at all.
            $icons[] = ['src' => $tenant->logo_url, 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'];
            $icons[] = ['src' => $tenant->logo_url, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'];
        }

        // Always present, even when the tenant has a logo — keeps the
        // manifest installable if that logo URL is ever unreachable.
        $icons[] = ['src' => asset('img/android-icon-192x192.png'), 'sizes' => '192x192', 'type' => 'image/png'];

        return [
            'name'             => $tenant->name,
            'short_name'       => Str::limit($tenant->name, 12, ''),
            'start_url'        => $startUrl,
            'display'          => 'standalone',
            'background_color' => '#ffffff',
            'theme_color'      => '#0078D4',
            'icons'            => $icons,
        ];
    }

    public static function response(Tenant $tenant, string $startUrl): JsonResponse
    {
        return response()->json(
            static::build($tenant, $startUrl),
            200,
            ['Content-Type' => 'application/manifest+json']
        );
    }
}
