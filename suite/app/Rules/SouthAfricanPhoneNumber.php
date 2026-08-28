<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates a South African phone/cell number, tolerant of the formats people
 * actually type: 0821234567, 082 123 4567, +27821234567, 27821234567,
 * with or without spaces/dashes. Landline and mobile share the same 10-digit
 * national format, so this doesn't try to distinguish them.
 */
class SouthAfricanPhoneNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $raw = (string) $value;

        // Only strip characters people actually type as formatting — a stray
        // letter or symbol should fail validation, not be silently dropped.
        if (! preg_match('/^[\d\s()+-]+$/', $raw)) {
            $fail('The :attribute must be a valid South African phone number (e.g. 082 123 4567).');
            return;
        }

        $digits = preg_replace('/[^\d+]/', '', $raw);

        // Normalize +27/27-prefixed numbers back to the 0-prefixed national form.
        if (str_starts_with($digits, '+27')) {
            $digits = '0' . substr($digits, 3);
        } elseif (str_starts_with($digits, '27') && strlen($digits) === 11) {
            $digits = '0' . substr($digits, 2);
        }

        if (! preg_match('/^0\d{9}$/', $digits)) {
            $fail('The :attribute must be a valid South African phone number (e.g. 082 123 4567).');
        }
    }
}
