<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'plug4market' => [
        'token' => env('PLUG4MARKET_TOKEN'),
        'refresh_token' => env('PLUG4MARKET_REFRESH_TOKEN'),
        'base_url' => env('PLUG4MARKET_BASE_URL', 'https://api.sandbox.plug4market.com.br'),
        'sandbox' => env('PLUG4MARKET_SANDBOX', true),
        'seller_id' => env('PLUG4MARKET_SELLER_ID', '7'),
        'software_house_cnpj' => env('PLUG4MARKET_SOFTWARE_HOUSE_CNPJ', '04026307000112'),
        'store_cnpj' => env('PLUG4MARKET_STORE_CNPJ', '04026307000112'),
        'user_id' => env('PLUG4MARKET_USER_ID', '89579395-cc99-4a2a-8bb9-8e2165d7611d'),
        'api_version' => env('PLUG4MARKET_API_VERSION', 'v1'),
        'timeout' => env('PLUG4MARKET_TIMEOUT', 30),
        'retry_attempts' => env('PLUG4MARKET_RETRY_ATTEMPTS', 3),
    ],

];
