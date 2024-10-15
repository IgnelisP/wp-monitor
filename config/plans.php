<?php

return [
    'stripe_key' => env('STRIPE_KEY'),

    'plans' => [
        'lite' => [
            'name' => 'Lite',
            'stripe_prices' => [
                '1_month' => [
                    'price_id' => 'price_1Px3mIBufZ7lf2kii0yaPjHW',
                    'price' => 7.99,
                    'type' => 'lite_1m',
                ],
                '12_months' => [
                    'price_id' => 'price_1Px3nhBufZ7lf2kiMbkkrcG3',
                    'price' => 69.99,
                    'type' => 'lite_12m',
                ],
                '24_months' => [
                    'price_id' => 'price_1Px3oYBufZ7lf2kiPgRL7HkZ',
                    'price' => 119.99,
                    'type' => 'lite_24m',
                ],
            ],
            'features' => [],
        ],

        'professional' => [
            'name' => 'Professional',
            'stripe_prices' => [
                '1_month' => [
                    'price_id' => 'price_1Px4MBBufZ7lf2kiZ7ojZVKh',
                    'price' => 11.99,
                    'type' => 'professional_1m',
                ],
                '12_months' => [
                    'price_id' => 'price_1Px4O3BufZ7lf2kidlnN2lJB',
                    'price' => 109.99,
                    'type' => 'professional_12m',
                ],
                '24_months' => [
                    'price_id' => 'price_1Px4P3BufZ7lf2kilqk3wdg0',
                    'price' => 199.99,
                    'type' => 'professional_24m',
                ],
            ],
            'features' => [],
        ],

        'enterprise' => [
            'name' => 'Enterprise',
            'stripe_prices' => [
                '1_month' => [
                    'price_id' => 'price_1Px4WbBufZ7lf2kiOcZrJNWZ',
                    'price' => 16.99,
                    'type' => 'enterprise_1m',
                ],
                '12_months' => [
                    'price_id' => 'price_1Px4XbBufZ7lf2kickCNqwmR',
                    'price' => 179.99,
                    'type' => 'enterprise_12m',
                ],
                '24_months' => [
                    'price_id' => 'price_1Px4YNBufZ7lf2kiiGPUkg7X',
                    'price' => 309.99,
                    'type' => 'enterprise_24m',
                ],
            ],
            'features' => [],
        ],
    ],
];