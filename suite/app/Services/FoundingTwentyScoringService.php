<?php

namespace App\Services;

use App\Models\FoundingTwentyApplication;

class FoundingTwentyScoringService
{
    private const APPOINTMENT_VOLUME_POINTS = [
        '0-50' => 2, '51-150' => 6, '151-300' => 9, '300+' => 10,
    ];

    private const NO_SHOW_POINTS = [
        '0' => 0, '1-2' => 3, '3-5' => 6, '6-10' => 9, '10+' => 10,
    ];

    private const HOURS_POINTS = [
        '<1' => 1, '1-3' => 3, '3-5' => 6, '5-10' => 8, '10+' => 10,
    ];

    private const STAFF_POINTS = [
        '1' => 2, '2-5' => 6, '6-10' => 9, '10+' => 10,
    ];

    private const LIKELIHOOD_POINTS = [
        'very_likely' => 10, 'likely' => 7.5, 'unsure' => 5, 'unlikely' => 2.5, 'very_unlikely' => 0,
    ];

    private const DIGITAL_SIGNAL_METHODS = [
        'website', 'booking_platform', 'google_calendar', 'banking_app', 'accounting_software', 'pos',
    ];

    private const MANUAL_ONLY_METHODS = ['notebook', 'none', 'paper_diary'];

    public function score(FoundingTwentyApplication $application): array
    {
        $appointmentVolume = self::APPOINTMENT_VOLUME_POINTS[$application->monthly_appointments] ?? 2;

        $operationalPain = $this->operationalPainScore($application);

        $noShowImpact = self::NO_SHOW_POINTS[$application->no_shows_per_month] ?? 0;

        $adminTime = $this->adminTimeScore($application);

        $staffComplexity = self::STAFF_POINTS[$application->staff_count] ?? 2;

        $paymentTrackingProblem = $this->paymentTrackingProblemScore($application);

        $technologyReadiness = $this->technologyReadinessScore($application);

        $interest = round((($application->value_rating ?? 1) - 1) / 4 * 10, 1);

        $feedbackWillingness = $this->feedbackWillingnessScore($application);

        $total = $appointmentVolume + $operationalPain + $noShowImpact + $adminTime
            + $staffComplexity + $paymentTrackingProblem + $technologyReadiness
            + $interest + $feedbackWillingness;

        $score = (int) round(min(100, max(0, $total)));

        return [
            'score' => $score,
            'tier' => $this->tierFor($score),
        ];
    }

    private function operationalPainScore(FoundingTwentyApplication $application): float
    {
        $fields = [
            'pain_forgotten_appointments', 'pain_late_cancellations', 'pain_no_shows', 'pain_double_bookings',
            'pain_booking_enquiry_time', 'pain_staff_availability', 'pain_tracking_balances',
            'pain_revenue_visibility', 'pain_customer_data_organisation',
        ];

        $values = array_filter(array_map(fn ($field) => $application->{$field}, $fields), fn ($v) => $v !== null);

        if (empty($values)) {
            return 0;
        }

        $average = array_sum($values) / count($values);

        return round(($average - 1) / 4 * 20, 1);
    }

    private function adminTimeScore(FoundingTwentyApplication $application): float
    {
        $fields = ['hours_booking_admin', 'hours_availability_messages', 'hours_manual_reminders'];

        $points = array_filter(array_map(
            fn ($field) => self::HOURS_POINTS[$application->{$field}] ?? null,
            $fields
        ), fn ($v) => $v !== null);

        if (empty($points)) {
            return 0;
        }

        return round(array_sum($points) / count($points), 1);
    }

    private function paymentTrackingProblemScore(FoundingTwentyApplication $application): float
    {
        $methods = array_merge(
            $application->payment_tracking_methods ?? [],
            $application->balance_tracking_methods ?? []
        );

        if (empty($methods)) {
            return 5;
        }

        $hasDigital = count(array_intersect($methods, self::DIGITAL_SIGNAL_METHODS)) > 0;
        $manualOnly = count(array_intersect($methods, self::MANUAL_ONLY_METHODS)) > 0 && !$hasDigital;

        return match (true) {
            $manualOnly => 10,
            !$hasDigital => 6,
            default => 2,
        };
    }

    private function technologyReadinessScore(FoundingTwentyApplication $application): float
    {
        $methods = array_merge(
            $application->booking_methods ?? [],
            $application->appointment_management_methods ?? [],
            $application->payment_tracking_methods ?? []
        );

        $digitalSignals = count(array_unique(array_intersect($methods, self::DIGITAL_SIGNAL_METHODS)));

        return match (true) {
            $digitalSignals >= 3 => 10,
            $digitalSignals === 2 => 8,
            $digitalSignals === 1 => 5,
            default => 2,
        };
    }

    private function feedbackWillingnessScore(FoundingTwentyApplication $application): float
    {
        $feedbackPoints = $application->willing_to_give_feedback ? 10 : 0;
        $likelihoodPoints = self::LIKELIHOOD_POINTS[$application->continuation_likelihood] ?? 5;

        return round(($feedbackPoints + $likelihoodPoints) / 2, 1);
    }

    private function tierFor(int $score): string
    {
        return match (true) {
            $score >= 80 => 'high',
            $score >= 60 => 'good',
            $score >= 40 => 'potential',
            default => 'low',
        };
    }
}
