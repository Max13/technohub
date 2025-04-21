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

    '3cx' => [
        'url' => env('THREECX_URL'),
        'party' => env('THREECX_PARTY'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT'),
        'allowed_domains' => explode(',', env('GOOGLE_ALLOWED_DOMAINS')),
        'wallet' => [
            'credentials' => env('GOOGLE_WALLET_CREDENTIALS'),
            'issuer' => env('GOOGLE_WALLET_ISSUER'),
            'class' => env('GOOGLE_WALLET_CLASS'),
        ],
    ],

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'mikrotik' => [
        'host' => env('MIKROTIK_HOST'),
        'baseUrl' => env('MIKROTIK_BASEURL'),
        'username' => env('MIKROTIK_USERNAME'),
        'password' => env('MIKROTIK_PASSWORD'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'ypareo' => [
        'apiKey' => env('YPAREO_APIKEY'),
        'baseUrl' => env('YPAREO_BASEURL'),
        // Cache keys
        'cache' => [
            'expiration' => 14400, // 4 hours in seconds
        ],
        'period' => 'ypareo.current_period',
    ],

];
