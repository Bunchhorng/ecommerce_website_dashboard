<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Mode
    |--------------------------------------------------------------------------
    |
    | "sandbox"    order confirmation is self-asserted (no real gateway). Only
    |              suitable for local/demo deployments.
    |
    | "production" order confirmation via the gated API is rejected until a real
    |              payment gateway is integrated, preventing orders from being
    |              marked paid without an actual payment.
    |
    | Defaults to "production" on production environments so a misconfigured
    | deployment stays safe-by-default.
    |
    */
    'payment_mode' => env('PAYMENT_MODE', env('APP_ENV') === 'production' ? 'production' : 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Reservation Window
    |--------------------------------------------------------------------------
    |
    | How long a checkout reservation is held before the scheduler releases it.
    |
    */
    'reservation_minutes' => (int) env('ORDER_RESERVATION_MINUTES', 15),
];