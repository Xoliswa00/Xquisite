<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates a 13-digit South African ID number: YYMMDD SSSS C A Z
 * (date of birth, gender sequence, citizenship, unused digit, Luhn check digit).
 *
 * Deliberately does NOT reject on the citizenship digit or the "A" digit being
 * an unexpected value — those have drifted in practice (Home Affairs has
 * issued IDs that don't strictly follow the historical citizenship=0/1 rule),
 * so this checks the two things that are still reliable: a real calendar date
 * in positions 1-6, and a correct Luhn checksum in the final digit.
 */
class SouthAfricanIdNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = (string) $value;

        if (! preg_match('/^\d{13}$/', $value)) {
            $fail('The :attribute must be a valid 13-digit South African ID number.');
            return;
        }

        if (! $this->hasValidDateOfBirth($value)) {
            $fail('The :attribute does not contain a valid date of birth.');
            return;
        }

        if (! $this->hasValidChecksum($value)) {
            $fail('The :attribute is not a valid South African ID number (checksum failed).');
        }
    }

    private function hasValidDateOfBirth(string $id): bool
    {
        $year  = (int) substr($id, 0, 2);
        $month = (int) substr($id, 2, 2);
        $day   = (int) substr($id, 4, 2);

        if ($month < 1 || $month > 12) {
            return false;
        }

        // Two-digit year is genuinely ambiguous (1900s vs 2000s) — try both
        // centuries and accept the date if either produces a real calendar day.
        return checkdate($month, $day, 1900 + $year) || checkdate($month, $day, 2000 + $year);
    }

    private function hasValidChecksum(string $id): bool
    {
        // Standard Luhn algorithm over all 13 digits.
        $sum     = 0;
        $double  = false;

        for ($i = strlen($id) - 1; $i >= 0; $i--) {
            $digit = (int) $id[$i];

            if ($double) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
            $double = ! $double;
        }

        return $sum % 10 === 0;
    }
}
