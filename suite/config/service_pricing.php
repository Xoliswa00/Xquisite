<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Project Pricing Reference (ZAR)
    |--------------------------------------------------------------------------
    | Starting-price ranges shown as a read-only reference panel while building
    | a Gig quote. These are guidance, not enforced limits — real quotes are
    | custom per client.
    |
    */

    'projects' => [
        ['label' => 'Business Website (5–10 pages)', 'min' => 8000, 'max' => 18000],
        ['label' => 'Booking Website', 'min' => 12000, 'max' => 25000],
        ['label' => 'E-commerce Website', 'min' => 18000, 'max' => 45000],
        ['label' => 'Power BI Dashboard', 'min' => 5000, 'max' => 20000],
        ['label' => 'Business Reporting Setup', 'min' => 3000, 'max' => 10000],
        ['label' => 'Workflow Automation', 'min' => 5000, 'max' => 30000],
        ['label' => 'Custom Business System', 'min' => 25000, 'max' => 150000, 'plus' => true],
        ['label' => 'Integrations / API Work', 'min' => 3000, 'max' => 20000],
    ],

    'hourly' => [
        ['label' => 'Development', 'min' => 450, 'max' => 750],
        ['label' => 'Analytics', 'min' => 500, 'max' => 850],
        ['label' => 'Consulting', 'min' => 750, 'max' => 1500],
    ],

];
