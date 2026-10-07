<?php

namespace App\Services\Security;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Booking\Models\Customer;
use App\Modules\Property\Models\Contractor;
use App\Modules\Property\Models\Renter;
use App\Notifications\AppNotice;
use App\Notifications\SignInPausedNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

/**
 * Tells the people who should know when a real account's sign-in is paused:
 * the account holder by email ("was this you?"), and for a team login, the
 * business owner in the app.
 *
 * Nothing here changes what the visitor at the sign-in form sees, so it gives
 * away nothing about whether the email belongs to an account.
 */
class SignInPauseNotifier
{
    private const LABELS = [
        'staff'      => 'team',
        'customer'   => 'booking',
        'renter'     => 'renter portal',
        'contractor' => 'contractor portal',
    ];

    public static function accountPaused(string $guard, string $email, ?string $tenantSlug): void
    {
        $tenant  = $tenantSlug ? Tenant::where('slug', $tenantSlug)->first() : null;
        $account = self::findAccount($guard, $email, $tenant);

        if (! $account) {
            return;
        }

        $tenant ??= $account->tenant ?? null;
        $minutes = (int) ceil(LoginThrottleService::LOCK_SECONDS / 60);

        // At most one email a day per account, so mistyping someone's password
        // on purpose can't be used to fill their inbox.
        if (Cache::add("sign_in_pause_mail:{$guard}:{$account->getKey()}", true, now()->addDay())) {
            $account->notify(new SignInPausedNotification(
                accountLabel: self::LABELS[$guard],
                businessName: $tenant?->name ?? config('app.name'),
                attempts: LoginThrottleService::ACCOUNT_THRESHOLD,
                minutes: $minutes,
                at: now()->format('H:i'),
                resetUrl: self::resetUrl($guard, $tenant?->slug),
            ));
        }

        // Customers and renters forget passwords all the time; only a paused
        // team login is worth the owner's attention.
        if ($guard !== 'staff' || ! $tenant) {
            return;
        }

        $owner = $tenant->owner();

        if (! $owner || $owner->is($account)) {
            return;
        }

        if (Cache::add("sign_in_pause_owner:{$tenant->id}:{$account->getKey()}", true, now()->addHour())) {
            $owner->notify(new AppNotice(
                title: 'Sign-in paused for a team member',
                message: "{$account->name} ({$account->email}) entered the wrong password " . LoginThrottleService::ACCOUNT_THRESHOLD
                    . " times, so sign-in for that login is paused for {$minutes} minutes. If they are locked out, you can set a new password for them. If it was not them, set a new password now.",
                url: Route::has('admin.users.index') ? route('admin.users.index') : null,
                level: 'warning',
            ));
        }
    }

    private static function findAccount(string $guard, string $email, ?Tenant $tenant): mixed
    {
        return match ($guard) {
            'staff'    => User::where('email', $email)->first(),
            'customer' => Customer::withoutGlobalScopes()->where('email', $email)
                ->when($tenant, fn ($q) => $q->where('tenant_id', $tenant->id))->first(),
            // Renter and contractor emails are only unique within one business.
            'renter'     => $tenant ? Renter::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('email', $email)->first() : null,
            'contractor' => $tenant ? Contractor::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('email', $email)->first() : null,
            default      => null,
        };
    }

    private static function resetUrl(string $guard, ?string $slug): string
    {
        return match (true) {
            $guard === 'customer' && $slug   => route('book.password.request', $slug),
            $guard === 'renter' && $slug     => route('rent.password.request', $slug),
            $guard === 'contractor' && $slug => route('contractor.password.request', $slug),
            default                          => route('password.request'),
        };
    }
}
