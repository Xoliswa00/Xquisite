<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingSetting extends Model
{
    protected $fillable = ['key', 'value'];

    private static array $defaults = [
        'grace_period_days'    => '5',
        'invoice_due_days'     => '7',
        'auto_billing_enabled' => '1',
        'billing_day_of_month' => '1',
    ];

    public static function get(string $key): ?string
    {
        return cache()->remember("billing_setting:{$key}", 3600, function () use ($key) {
            $row = static::where('key', $key)->first();
            return $row?->value ?? static::$defaults[$key] ?? null;
        });
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        cache()->forget("billing_setting:{$key}");
    }

    public static function getSettings(): array
    {
        $db = static::query()->pluck('value', 'key')->toArray();
        return array_merge(static::$defaults, $db);
    }
}
