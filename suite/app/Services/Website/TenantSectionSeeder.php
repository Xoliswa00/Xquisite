<?php

namespace App\Services\Website;

use App\Models\Tenant;
use App\Models\TenantBranding;
use App\Models\TenantPageSection;
use App\Models\Template;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TenantSectionSeeder
{
    /**
     * Seeds a tenant's page sections from their template's preset the first
     * time they have none — idempotent, never overwrites existing sections
     * (a tenant who has already edited their page keeps their edits).
     */
    public static function seedForTenant(Tenant $tenant, Template $template): void
    {
        if ($template->isPlaceholder()) {
            return;
        }

        if (TenantPageSection::where('tenant_id', $tenant->id)->exists()) {
            return;
        }

        $preset = TemplatePresetRegistry::presetFor($template->key) ?? ['sections' => []];
        $branding = $tenant->branding;

        DB::transaction(function () use ($tenant, $preset, $branding) {
            foreach (($preset['sections'] ?? []) as $i => $def) {
                TenantPageSection::create([
                    'tenant_id'  => $tenant->id,
                    'type'       => $def['type'],
                    'variant'    => $def['variant'] ?? null,
                    'content'    => self::hydrateFromBranding($def['type'], $def['content'] ?? [], $branding),
                    'sort_order' => $i,
                    'is_visible' => true,
                ]);
            }
        });
    }

    /**
     * Non-persisted sections for a template that has no real tenant yet
     * (the marketplace's fake-tenant preview thumbnails/iframes).
     */
    public static function virtualSectionsFor(Template $template): Collection
    {
        if ($template->isPlaceholder()) {
            return collect();
        }

        $preset = TemplatePresetRegistry::presetFor($template->key) ?? ['sections' => []];

        return collect($preset['sections'] ?? [])->values()->map(function (array $def, int $i) {
            return new TenantPageSection([
                'type'       => $def['type'],
                'variant'    => $def['variant'] ?? null,
                'content'    => $def['content'] ?? [],
                'sort_order' => $i,
                'is_visible' => true,
            ]);
        });
    }

    /**
     * Defensive, never throws — called from BrandingController's existing
     * (wizard-facing) update methods so edits made there keep showing up in
     * the tenant's sections without those methods needing to know sections
     * exist at all.
     */
    public static function syncBrandingFieldToSections(Tenant $tenant, string $field, mixed $value): void
    {
        try {
            $targetType = match ($field) {
                'hero_image_url' => 'hero',
                'about_image_url', 'description' => 'about',
                'gallery_images' => 'gallery',
                'contact_email', 'contact_phone', 'whatsapp_number', 'socials', 'business_hours' => 'contact',
                default => null,
            };

            if (! $targetType) {
                return;
            }

            $section = TenantPageSection::where('tenant_id', $tenant->id)
                ->where('type', $targetType)
                ->orderBy('sort_order')
                ->first();

            if (! $section) {
                return;
            }

            $content = $section->content ?? [];

            match ($field) {
                'hero_image_url' => $content['background_image_url'] = $value,
                'about_image_url' => $content['image_url'] = $value,
                'description' => $content['body'] = $value,
                'gallery_images' => $content['images'] = collect($value ?? [])->map(fn ($url) => ['url' => $url, 'tags' => []])->all(),
                'contact_email' => $content['email'] = $value,
                'contact_phone' => $content['phone'] = $value,
                'whatsapp_number' => $content['whatsapp_number'] = $value,
                'socials' => $content['socials'] = $value,
                'business_hours' => $content['business_hours'] = $value,
                default => null,
            };

            $section->update(['content' => $content]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private static function hydrateFromBranding(string $type, array $content, ?TenantBranding $branding): array
    {
        return match ($type) {
            'hero' => [
                ...$content,
                'background_image_url' => $branding?->hero_image_url ?? $content['background_image_url'] ?? null,
            ],
            'about' => [
                ...$content,
                'body' => $branding?->description ?? $content['body'] ?? null,
                'image_url' => $branding?->about_image_url ?? $content['image_url'] ?? null,
            ],
            'gallery' => [
                ...$content,
                'images' => ! empty($branding?->gallery_images)
                    ? collect($branding->gallery_images)->map(fn ($url) => ['url' => $url, 'tags' => []])->all()
                    : ($content['images'] ?? []),
            ],
            'contact' => [
                ...$content,
                'email' => $branding?->contact_email,
                'phone' => $branding?->contact_phone,
                'whatsapp_number' => $branding?->whatsapp_number,
                'business_hours' => $branding?->business_hours ?? [],
                'socials' => $branding?->socials ?? [],
            ],
            default => $content,
        };
    }
}
