<?php

return [
    // Mandatory Configuration Options
    'hosts' => explode(',', env('LDAP_HOSTS')),
    'base_dn' => 'dc=tech,dc=iticparis,dc=com',
    'username' => env('LDAP_USERNAME'),
    'password' => env('LDAP_PASSWORD'),

    // Optional Configuration Options
    'port' => 389,
    'protocol' => 'ldap://',
    'use_ssl' => false,
    'use_tls' => false,
    'use_sasl' => false,
    'version' => 3,
    'timeout' => 5,
    'follow_referrals' => false,

    // Custom LDAP Options
    'options' => [
        // See: http://php.net/ldap_set_option
        LDAP_OPT_X_TLS_REQUIRE_CERT => LDAP_OPT_X_TLS_HARD
    ],

    // See: https://www.php.net/manual/en/function.ldap-sasl-bind.php
    'sasl_options' => [
        'mech' => null,
        'realm' => null,
        'authc_id' => null,
        'authz_id' => null,
        'props' => null,
    ],
];
