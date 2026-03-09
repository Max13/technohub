<?php

use EbicsApi\Ebics\Models\Keyring;

return [

    'url' => env('EBICS_URL'),
    'host_id' => env('EBICS_HOST_ID'),
    'partner_id' => env('EBICS_PARTNER_ID'),
    'user_id' => env('EBICS_USER_ID'),
    'version' => Keyring::VERSION_30,
    'path' => env('EBICS_PATH', storage_path('app/ebics')),

    'bank_certificates' => [
        'x002' => env('EBICS_X002'),
        'e002' => env('EBICS_E002'),
    ],

    'keyring_password' => env('EBICS_KEYRING_PASSWORD'),
];
