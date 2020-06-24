<?php

return [
    'pricebook' => env('OSF_PRICEBOOK', null),
    'regions' => [
        'uk' => [
            'currency' => 'GBP',
            'office' => 'UK',
            'pricebook' => env('OSF_PRICEBOOK_UK', null),
            'source' => env('OSF_SOURCE_UK'),
        ],
        'nl' => [
            'currency' => 'EUR',
            'office' => 'NL',
            'pricebook' => env('OSF_PRICEBOOK_NL', null),
            'source' => env('OSF_SOURCE_NL'),
        ],
        'us' => [
            'currency' => 'USD',
            'office' => 'US',
            'pricebook' => env('OSF_PRICEBOOK_US', null),
            'source' => env('OSF_SOURCE_US'),
        ]
    ]
];
