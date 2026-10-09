<?php

namespace App\Support;

use App\Modules\Booking\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * What a customer typed to sign in with (an email address or a cell number),
 * read the same way everywhere.
 *
 * The middleware that refuses a paused sign-in and the controller that counts
 * the failures must agree on this, or the pause checks one name while the
 * strikes land on another.
 */
class SignInIdentifier
{
    /** `login` is the field; `email` is still read so an older open page keeps working. */
    public static function typed(Request $request): ?string
    {
        foreach (['login', 'email'] as $field) {
            $value = $request->input($field);

            if (is_string($value) && trim($value) !== '') {
                return Str::limit(trim($value), 190, '');
            }
        }

        return null;
    }

    /**
     * The name strikes are counted under. A cell number is reduced to one form,
     * so spaces, dashes or +27 can't be used to earn a fresh set of tries.
     */
    public static function canonical(?string $typed): ?string
    {
        if ($typed === null || str_contains($typed, '@')) {
            return $typed;
        }

        return Customer::normalisePhone($typed) ?? $typed;
    }
}
