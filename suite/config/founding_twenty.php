<?php

return [
    'deposit_amount' => env('FOUNDING_TWENTY_DEPOSIT_AMOUNT', 100.00),

    // What a business pays per month once the free period ends.
    'monthly_price' => env('FOUNDING_TWENTY_MONTHLY_PRICE', 200),

    // Free months at the start, and how long the monthly price is guaranteed after they end.
    'free_months' => 3,
    'price_lock_months' => 24,

    // Promise made to every applicant on the thank-you page and in the received message.
    'decision_within_days' => 7,

    // Chasing thresholds shown in the admin action queue.
    'activation_days' => 14,          // onboarded but no first win logged after this long
    'reservation_chase_days' => 5,    // selected but no deposit proof after this long
    'conversion_notice_day' => 76,    // day of the free period to send the "what happens next" offer (90 - 14)

    // What success looks like, shown against actuals on the funnel page.
    // Placeholders to be confirmed by Xoliswa before launch.
    'targets' => [
        'applications' => 40,
        'selected' => 20,
        'activated' => 16,   // logged a first win within activation_days
        'paying' => 12,      // still active and paying after the free period
    ],
];
