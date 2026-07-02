<?php

return [
    // Public base URL used to build absolute menu image URLs in the public feed.
    // Images live at deterministic paths: {public_url}/menu/{category}/{item}.webp
    'public_url' => rtrim(env('ADMIN_PUBLIC_URL', env('APP_URL', 'https://api.admin.genzfoods.pk')), '/'),
];
