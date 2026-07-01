<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

/**
 * Normalises and stores menu images at deterministic paths on the `menu` disk:
 *   {category-slug}/{slug}.webp        (display, scaled down to max 1000px)
 *   {category-slug}/{slug}-thumb.webp  (list/thumbnail, 400x400 cover)
 *
 * Re-uploading overwrites in place, so the URL never changes — callers bump the
 * owning record's image_updated_at for cache-busting (?v= in the feed).
 */
class ImageService
{
    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = ImageManager::usingDriver(new Driver);
    }

    /** Store the display + thumbnail webp for a slug under its category. */
    public function store(UploadedFile $file, string $categorySlug, string $slug): void
    {
        $disk = Storage::disk('menu');
        $path = $file->getRealPath();

        $full = $this->manager->decodePath($path)
            ->scaleDown(width: 1000, height: 1000)
            ->encode(new WebpEncoder(quality: 82));
        $disk->put("{$categorySlug}/{$slug}.webp", (string) $full);

        $thumb = $this->manager->decodePath($path)
            ->cover(400, 400)
            ->encode(new WebpEncoder(quality: 80));
        $disk->put("{$categorySlug}/{$slug}-thumb.webp", (string) $thumb);
    }

    /** Remove both webp renditions for a slug (e.g. on delete). */
    public function delete(string $categorySlug, string $slug): void
    {
        $disk = Storage::disk('menu');
        $disk->delete(["{$categorySlug}/{$slug}.webp", "{$categorySlug}/{$slug}-thumb.webp"]);
    }
}
