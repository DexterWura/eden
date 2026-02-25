<?php

return [
    'features' => [
        'blogging' => [
            'name' => 'Blogging',
            'description' => 'Write and publish SEO-rich blog posts (admin and startup owners).',
        ],
    ],

    'default_prices' => [
        'blogging' => 10.00,
    ],

    'currencies' => [
        'USD' => 'US Dollar',
        'ZWL' => 'Zimbabwe Dollar',
    ],

    'gateways' => [
        'paypal' => 'PayPal',
        'paynow' => 'PayNow Zimbabwe',
    ],
];
