<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Scheme Redemption Threshold
    |--------------------------------------------------------------------------
    |
    | The minimum amount a customer must accumulate from scheme products
    | before they become eligible for bonus redemption.
    | Default: 50000 (₹50,000)
    |
    */
    'redemption_threshold' => env('SCHEME_REDEMPTION_THRESHOLD', 50000),

    /*
    |--------------------------------------------------------------------------
    | Scheme Bonus Amount
    |--------------------------------------------------------------------------
    |
    | The fixed bonus amount awarded to customers when they reach the
    | redemption threshold.
    | Default: 5000 (₹5,000)
    |
    */
    'bonus_amount' => env('SCHEME_BONUS_AMOUNT', 5000),

    /*
    |--------------------------------------------------------------------------
    | Maximum Redemption Percentage
    |--------------------------------------------------------------------------
    |
    | The maximum percentage of order total that can be redeemed from
    | available bonus amount.
    | Default: 50 (50%)
    |
    */
    'max_redemption_percentage' => env('SCHEME_MAX_REDEMPTION_PERCENTAGE', 50),
];
