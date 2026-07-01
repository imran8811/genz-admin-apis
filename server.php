<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * Router script for `php artisan serve` / the PHP built-in web server.
 * Provided explicitly because this framework install is missing the bundled
 * resources/server.php that ServeCommand falls back to.
 */
$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server. This provides a convenient way to test a Laravel
// application without having installed a "real" web server software here.
if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

require_once __DIR__.'/public/index.php';
