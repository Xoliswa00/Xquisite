<?php

namespace App\Services\Website;

class TemplatePresetRegistry
{
    /**
     * Keys whose plain "site-templates/..." string values (config data,
     * never asset()-wrapped — see the comment atop the config file) need
     * resolving to a full URL before use.
     */
    private const ASSET_KEYS = [
        'background_image_url', 'image_url', 'photo_url', 'avatar_url',
        'poster_image_url', 'url',
    ];

    public static function presetFor(string $templateKey): ?array
    {
        $preset = config("website_template_presets.{$templateKey}");

        return $preset === null ? null : self::resolveAssetUrls($preset);
    }

    public static function hasPreset(string $templateKey): bool
    {
        return config("website_template_presets.{$templateKey}") !== null;
    }

    private static function resolveAssetUrls(array $value): array
    {
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $value[$key] = self::resolveAssetUrls($item);
                continue;
            }

            if (is_string($item) && in_array($key, self::ASSET_KEYS, true) && ! str_starts_with($item, 'http')) {
                $value[$key] = asset($item);
            }
        }

        return $value;
    }
}
