<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MenuItemController;
use App\Http\Controllers\Api\PublicMenuController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'app' => config('app.name'),
    'time' => now()->toISOString(),
]));

// Public, read-only canonical menu feed (source of truth for web/app/RMS/web-apis).
Route::get('/public/menu', [PublicMenuController::class, 'index']);

// Admin auth
Route::post('/auth/login', [AuthController::class, 'login']);

// Admin (Sanctum-protected) — menu CRUD + image uploads
Route::middleware('auth:sanctum')->prefix('admin')->group(function (): void {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::post('/categories/reorder', [CategoryController::class, 'reorder']);
    Route::post('/categories/{category}/image', [CategoryController::class, 'uploadImage']);
    Route::apiResource('categories', CategoryController::class);

    Route::post('/menu-items/reorder', [MenuItemController::class, 'reorder']);
    Route::post('/menu-items/{menuItem}/image', [MenuItemController::class, 'uploadImage']);
    Route::apiResource('menu-items', MenuItemController::class);
});
