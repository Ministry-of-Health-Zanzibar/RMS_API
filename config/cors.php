<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // ⚠️ MUST be explicit origin, not *
    'allowed_origins' => [
        'http://102.223.7.168',
        'http://102.223.7.168:4200' // if Angular dev server
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [
        'Authorization'
    ],

    'max_age' => 0,

    // ✅ REQUIRED for login/auth
    'supports_credentials' => true,
];

