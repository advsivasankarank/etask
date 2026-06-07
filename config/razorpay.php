<?php

declare(strict_types=1);

return [
    'enabled' => false,
    'key_id' => env('RAZORPAY_KEY_ID', ''),
    'key_secret' => env('RAZORPAY_KEY_SECRET', ''),
    'currency' => env('RAZORPAY_CURRENCY', 'INR'),
];
