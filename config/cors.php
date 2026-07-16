<?php

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing (CORS) Configuration
|--------------------------------------------------------------------------
|
| The admin SPA (admin.genzfoods.pk) calls this API on a different subdomain
| (api.admin.genzfoods.pk), so the browser sends CORS preflight requests.
| Auth is Bearer-token (Authorization header), not cookies, so credentialed
| CORS is not required — we just need to allow the admin origin(s).
|
| Override the allowed origins per-environment with CORS_ALLOWED_ORIGINS
| (comma-separated) in .env.
|
*/

$allowedOrigins = array_values(array_filter(array_map(
    static fn (string $origin): string => trim($origin),
    explode(',', (string) env(
        'CORS_ALLOWED_ORIGINS',
        // admin UI + storefront + RMS POS (all read the public menu feed directly),
        // plus local dev ports (admin :4300, web :4200, RMS POS :3000).
        'https://admin.genzfoods.pk,https://genzfoods.pk,https://www.genzfoods.pk,https://rms.genzfoods.pk,'
        .'http://localhost:4300,http://localhost:4200,http://localhost:3000'
    ))
)));

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 3600,

    'supports_credentials' => false,

];
