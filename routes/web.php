<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

// Menu images normally go out as static files from public/menu. When the web server can't find
// one there (e.g. a shared host whose document root isn't this app's public/), the request falls
// through to Laravel, which serves it from the `menu` disk instead.
Route::get('/menu/{category}/{file}', function (string $category, string $file) {
    $disk = Storage::disk('menu');
    $path = "{$category}/{$file}";
    abort_unless($disk->exists($path), 404);

    return response()->file($disk->path($path), [
        'Content-Type' => 'image/webp',
        'Cache-Control' => 'public, max-age=604800',
    ]);
})->where(['category' => '[a-z0-9-]+', 'file' => '[a-z0-9-]+\.webp']);
