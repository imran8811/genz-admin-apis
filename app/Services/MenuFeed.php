<?php

namespace App\Services;

use App\Models\Category;
use App\Models\MenuItem;

/**
 * Builds the canonical, read-only menu feed (menu.json shape) consumed directly
 * by genz-web / genz-app and synced by genz-web-apis (checkout re-pricing) and
 * genz-rms-apis (costing). The shape is byte-compatible with the feed the RMS
 * used to publish, PLUS an `image` URL per item (and category) when one exists.
 *
 * Image URLs are deterministic and cache-busted by image_updated_at:
 *   {public_url}/menu/{category-slug}/{item-slug}.webp?v={unix-ts}
 */
class MenuFeed
{
    public function build(): array
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->with(['menuItems' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->get()
            ->map(fn (Category $cat) => array_filter([
                'id' => $cat->slug,
                'name' => $cat->name,
                'type' => $cat->type,
                'sizes' => $cat->sizes,
                'image' => $this->imageUrl($cat->slug, $cat->slug, $cat->image_updated_at),
                'items' => $cat->menuItems->map(fn (MenuItem $item) => $this->mapItem($item, $cat->slug))->values(),
            ], fn ($v) => $v !== null))
            ->values();

        return [
            'generated_at' => now()->toISOString(),
            'categories' => $categories,
        ];
    }

    private function mapItem(MenuItem $item, string $categorySlug): array
    {
        $data = [
            'id' => $item->slug,
            'name' => $item->name,
            'description' => $item->description,
        ];

        if ($item->price_type === 'sized') {
            $data['prices'] = $item->prices ?? [];
        } else {
            $data['price'] = $item->price;
        }

        if ($item->is_special) {
            $data['special'] = true;
        }
        if ($item->is_signature) {
            $data['signature'] = true;
        }
        if ($item->tag) {
            $data['tag'] = $item->tag;
        }
        if (! empty($item->pizza_selection)) {
            $data['pizzaSelection'] = $item->pizza_selection;
        }
        if (! empty($item->deal_extras)) {
            $data['dealExtras'] = $item->deal_extras;
        }
        if ($url = $this->imageUrl($categorySlug, $item->slug, $item->image_updated_at)) {
            $data['image'] = $url;
        }

        return $data;
    }

    private function imageUrl(string $categorySlug, string $slug, $updatedAt): ?string
    {
        if ($updatedAt === null) {
            return null;
        }

        $base = config('genz.public_url');

        return "{$base}/menu/{$categorySlug}/{$slug}.webp?v={$updatedAt->timestamp}";
    }
}
