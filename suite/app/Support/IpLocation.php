<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class IpLocation
{
    public static function get(?string $ip): ?string
    {
        $data = self::lookup($ip);

        if (!$data) {
            return null;
        }

        return implode(', ', array_filter([
            $data['city'] ?? null,
            $data['regionName'] ?? null,
            $data['country'] ?? null,
        ]));
    }

    /**
     * Full lookup including coordinates, for map display. IP-to-location is
     * effectively static, so results are cached for a month to avoid hammering
     * the free ip-api.com quota on every page load.
     *
     * @return array{city:?string,regionName:?string,country:?string,lat:?float,lon:?float}|null
     */
    public static function geocode(?string $ip): ?array
    {
        if (!$ip || in_array($ip, ['127.0.0.1', '::1', ''])) {
            return null;
        }

        return Cache::remember("ip_geo:{$ip}", now()->addMonth(), fn () => self::lookup($ip));
    }

    private static function lookup(?string $ip): ?array
    {
        if (!$ip || in_array($ip, ['127.0.0.1', '::1', ''])) {
            return null;
        }

        try {
            $data = Http::timeout(1)->get("http://ip-api.com/json/{$ip}?fields=city,regionName,country,lat,lon")->json();

            if (($data['status'] ?? '') !== 'success') {
                return null;
            }

            return [
                'city'       => $data['city'] ?? null,
                'regionName' => $data['regionName'] ?? null,
                'country'    => $data['country'] ?? null,
                'lat'        => $data['lat'] ?? null,
                'lon'        => $data['lon'] ?? null,
            ];
        } catch (\Throwable) {
            return null;
        }
    }
}
