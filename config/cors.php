<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Explicitly list all origins you need: local + server
    'allowed_origins' => [
        'http://localhost:4200',        // Angular dev server local
        'http://127.0.0.1:4200',        // optional for some dev setups
        'http://102.223.7.168',         // server backend
        'http://102.223.7.168:4200',    // Angular dev server on server IP
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [
        'Authorization', // expose JWT or token headers
    ],

    'max_age' => 0,

    // Required for login/auth if sending cookies
    'supports_credentials' => true,
];

