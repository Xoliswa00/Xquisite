<?php

namespace App\Services\Booking;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Booking\Models\Customer;
use App\Notifications\QueuedAppNotice;
use Illuminate\Support\Facades\Cache;

/**
 * A customer asked, from the booking page, to be sent a login link. Tell the
 * team members who look after customers, with a link straight to that profile.
 */
class SetupLinkRequestNotifier
{
    /** One notice per customer in this window, however often the button is pressed. */
    private const QUIET_HOURS = 6;

    public static function requested(Tenant $tenant, Customer $customer): void
    {
        if (! Cache::add("setup_link_request:{$customer->id}", true, now()->addHours(self::QUIET_HOURS))) {
            return;
        }

        $hasLogin = filled($customer->password);
        $contact  = $customer->phone ?: $customer->email;

        $notice = new QueuedAppNotice(
            title: "{$customer->name} asked for a login link",
            message: "{$customer->name}" . ($contact ? " ({$contact})" : '') . ' asked from your booking page to be sent '
                . ($hasLogin
                    ? 'a new login link. They already have a login, so a manager or owner needs to create it.'
                    : 'their login setup link.')
                . ' Open their profile to create the link and send it.',
            url: route('customers.show', $customer->id),
            level: 'info',
        );

        User::where('tenant_id', $tenant->id)->where('is_active', true)->get()
            ->filter(fn (User $user) => $user->can('manage-customers') && (! $hasLogin || $user->isAdmin()))
            ->each->notify($notice);
    }
}
