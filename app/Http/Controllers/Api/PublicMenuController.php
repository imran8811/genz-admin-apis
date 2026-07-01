<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MenuFeed;
use Illuminate\Http\JsonResponse;

/**
 * Public, read-only canonical menu feed — the single source of truth consumed
 * directly by genz-web / genz-app and synced by genz-web-apis & genz-rms-apis.
 * Byte-compatible with the legacy RMS feed, plus image URLs (see MenuFeed).
 */
class PublicMenuController extends Controller
{
    public function index(MenuFeed $feed): JsonResponse
    {
        return response()->json($feed->build());
    }
}
