<?php

return [
    'default' => env('OSF_DEFAULT_REGION', 'uk'),
    'pricebook' => env('OSF_PRICEBOOK_ID', ''),
    'regions' => [
        'uk' => [
            'locale' => 'en_GB',
            'currency' => 'GBP',
            'office' => 'UK',
            'pricebook' => env('OSF_PRICEBOOK_ID_UK', ''),
            'source' => env('OSF_SOURCE_UK', ''),
        ],
        'nl' => [
            'locale' => 'nl_NL',
            'currency' => 'EUR',
            'office' => 'NL',
            'pricebook' => env('OSF_PRICEBOOK_ID_NL', ''),
            'source' => env('OSF_SOURCE_NL', ''),
        ],
        'us' => [
            'locale' => 'en_US',
            'currency' => 'USD',
            'office' => 'US',
            'pricebook' => env('OSF_PRICEBOOK_ID_US', ''),
            'source' => env('OSF_SOURCE_US', ''),
        ]
    ]
];
