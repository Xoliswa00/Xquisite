<?php

namespace App\Services\Website;

use InvalidArgumentException;

class SectionTypeRegistry
{
    public static function all(): array
    {
        return config('website_sections');
    }

    public static function exists(string $type): bool
    {
        return array_key_exists($type, self::all());
    }

    public static function get(string $type): array
    {
        return self::all()[$type]
            ?? throw new InvalidArgumentException("Unknown section type [{$type}].");
    }

    public static function fieldsFor(string $type): array
    {
        return self::get($type)['fields'] ?? [];
    }

    public static function variantsFor(string $type): array
    {
        return self::get($type)['variants'] ?? [];
    }

    public static function defaultVariantFor(string $type): string
    {
        return self::get($type)['default_variant'] ?? array_key_first(self::variantsFor($type)) ?? 'default';
    }

    public static function defaultContentFor(string $type): array
    {
        return self::get($type)['default_content'] ?? [];
    }

    public static function labelFor(string $type): string
    {
        return self::get($type)['label'] ?? ucfirst(str_replace('_', ' ', $type));
    }

    /**
     * Derives Laravel validation rules from the field schema, for
     * PageEditorController::update(). Repeater rows are validated loosely
     * (array of arrays) — per-item shape is developer-controlled via the
     * Blade repeater field, not user free text.
     */
    public static function validationRulesFor(string $type): array
    {
        $rules = [];

        foreach (self::fieldsFor($type) as $field) {
            $key = 'content.' . $field['key'];

            $rules[$key] = match ($field['type']) {
                'repeater' => 'nullable|array',
                'select'   => 'nullable|string',
                'image', 'url' => 'nullable|string|max:2048',
                'textarea' => 'nullable|string|max:' . ($field['max'] ?? 2000),
                default    => 'nullable|string|max:' . ($field['max'] ?? 255),
            };
        }

        return $rules;
    }

    /**
     * Resolves the field definition at a dot-path into a section's content
     * tree (e.g. "items" or the nested "items.0.features"), skipping purely
     * numeric segments since those are array indices, not field keys. Used
     * by PageEditorController::update() to build a blank default row when
     * a repeater's "+ Add" button is pressed.
     */
    public static function fieldDefinitionAtPath(string $type, string $dotPath): ?array
    {
        $segments = array_values(array_filter(
            explode('.', $dotPath),
            fn ($segment) => ! ctype_digit($segment)
        ));

        $fields = self::fieldsFor($type);
        $field = null;

        foreach ($segments as $segment) {
            $field = collect($fields)->firstWhere('key', $segment);

            if (! $field) {
                return null;
            }

            $fields = $field['item_fields'] ?? [];
        }

        return $field;
    }

    /**
     * A blank row for a repeater field, keyed by each item_field's key.
     */
    public static function blankRepeaterRow(array $repeaterField): array
    {
        return collect($repeaterField['item_fields'] ?? [])
            ->mapWithKeys(fn ($f) => [$f['key'] => $f['type'] === 'repeater' ? [] : null])
            ->all();
    }
}
