<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

class IpLocation
{
    public static function get(?string $ip): ?string
    {
        if (!$ip || in_array($ip, ['127.0.0.1', '::1', ''])) {
            return null;
        }

        try {
            $data = Http::timeout(1)->get("http://ip-api.com/json/{$ip}?fields=city,regionName,country")->json();

            if (($data['status'] ?? '') !== 'success') {
                return null;
            }

            return implode(', ', array_filter([
                $data['city'] ?? null,
                $data['regionName'] ?? null,
                $data['country'] ?? null,
            ]));
        } catch (\Throwable) {
            return null;
        }
    }
}
