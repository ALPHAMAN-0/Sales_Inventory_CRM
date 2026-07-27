<?php

return [

    /*
    | Paths the SPA hits that must send/receive the session + XSRF cookies.
    | Includes the Sanctum CSRF endpoint and the auth routes.
    */
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],

    'allowed_methods' => ['*'],

    /*
    | The SPA origin. NEVER '*' when supports_credentials is true — browsers
    | reject a wildcard origin on credentialed requests.
    */
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    /*
    | Required so the browser will store/send the Sanctum session cookie.
    */
    'supports_credentials' => true,
];
