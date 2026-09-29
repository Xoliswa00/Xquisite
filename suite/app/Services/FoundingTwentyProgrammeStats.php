<?php

namespace App\Services;

use App\Models\FoundingTwentyApplication;
use App\Models\FoundingTwentyCheckin;
use App\Models\PromoCodeRedemption;

/**
 * Everything the programme needs a person for, and the headline numbers for the
 * owner dashboard. The action queue page and the dashboard both read from here.
 */
class FoundingTwentyProgrammeStats
{
    public function __construct(private FoundingTwentyDepositLedger $ledger)
    {
    }

    /** The action queue sections, each a collection of applications (or check-ins). */
    public function queue(): array
    {
        $submitted = fn () => FoundingTwentyApplication::submitted();

        return [
            'depositsAwaitingConfirmation' => FoundingTwentyApplication::whereNotNull('deposit_submitted_at')
                ->whereNull('deposit_confirmed_at')->orderBy('deposit_submitted_at')->get(),

            'incompleteCheckins' => FoundingTwentyCheckin::whereNull('completed_at')->with('application')->orderBy('created_at')->get(),

            // Applicants nobody has acknowledged yet (no email on file, so nothing went out automatically).
            'toAcknowledge' => $submitted()->whereNull('received_notified_at')->where('status', 'pending')->orderBy('submitted_at')->get(),

            // Decided, but the applicant has not been told. The most damaging silence.
            'toTellDecision' => $submitted()->whereIn('status', ['selected', 'waitlisted', 'rejected'])
                ->whereNull('decision_notified_at')->orderBy('reviewed_at')->get(),

            // Waiting on a decision from us for longer than we promised.
            'overduePromise' => $submitted()->where('status', 'pending')
                ->where('submitted_at', '<', now()->subDays(config('founding_twenty.decision_within_days')))
                ->orderBy('submitted_at')->get(),

            // Selected but gone quiet before paying the deposit.
            'reservationChase' => $submitted()->where('status', 'selected')->whereNull('deposit_submitted_at')
                ->where('decision_notified_at', '<', now()->subDays(config('founding_twenty.reservation_chase_days')))
                ->orderBy('decision_notified_at')->get(),

            // Onboarded but no first win logged: the strongest early sign someone will not stay.
            'stuckOnboarding' => FoundingTwentyApplication::whereNotNull('tenant_linked_at')->whereNull('first_value_milestone_at')
                ->where('tenant_linked_at', '<', now()->subDays(config('founding_twenty.activation_days')))
                ->whereNull('activation_nudge_sent_at')->orderBy('tenant_linked_at')->get(),

            // Free period ending soon and they have not been told what happens next.
            'conversionDue' => FoundingTwentyApplication::whereNotNull('tenant_linked_at')->where('status', '!=', 'converted')
                ->where('tenant_linked_at', '<=', now()->subDays(config('founding_twenty.conversion_notice_day')))
                ->whereNull('conversion_offer_sent_at')->orderBy('tenant_linked_at')->get(),

            // Deposit the business has said how it wants back (or that has sat past the free period), not yet settled.
            'depositsToSettle' => FoundingTwentyApplication::whereNotNull('deposit_confirmed_at')
                ->whereNull('deposit_refunded_at')->whereNull('deposit_credited_at')
                ->whereNotNull('deposit_outcome')->orderBy('deposit_outcome_at')->get(),
        ];
    }

    /** How many separate things are waiting on a person. */
    public function actionCount(?array $queue = null): int
    {
        $queue ??= $this->queue();

        return collect($queue)->except('incompleteCheckins')->sum(fn ($c) => $c->count())
            + $queue['incompleteCheckins']->filter(fn ($c) => $c->created_at->diffInDays(now()) > 7)->count();
    }

    /** Headline numbers for the owner dashboard. Money comes from the deposit journal. */
    public function dashboard(): array
    {
        $submitted = FoundingTwentyApplication::submitted()->get();
        $deposits = $this->ledger->totals();
        $price = (float) config('founding_twenty.monthly_price');
        $paying = $submitted->where('status', 'converted')->count();

        return [
            'applications' => $submitted->count(),
            'unfinished' => FoundingTwentyApplication::leads()->count(),
            'selected' => $submitted->whereIn('status', ['selected', 'converted'])->count(),
            'onboarded' => $submitted->whereNotNull('tenant_linked_at')->count(),
            'activated' => $submitted->whereNotNull('first_value_milestone_at')->count(),
            'paying' => $paying,
            'targets' => config('founding_twenty.targets'),
            'deposits' => $deposits,
            'discrepancies' => $this->ledger->discrepancies()->count(),
            // Free months and referral rewards given away (what the programme costs us).
            'valueGiven' => (float) PromoCodeRedemption::whereNotNull('founding_twenty_application_id')->sum('financial_value'),
            // Monthly revenue already committed by businesses that carried on, at the locked price.
            'committedMonthly' => $paying * $price,
            'actionCount' => $this->actionCount(),
        ];
    }
}
