<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MenuItem;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MenuItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = MenuItem::with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('category_slug')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category_slug));
        }

        return response()->json($query->orderBy('sort_order')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateItem($request);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        if (MenuItem::where('slug', $data['slug'])->exists()) {
            return response()->json(['message' => 'An item with this slug already exists.'], 422);
        }

        $data['price_type'] = $data['price_type'] ?? Category::find($data['category_id'])?->type ?? 'single';

        return response()->json(MenuItem::create($data), 201);
    }

    public function show(MenuItem $menuItem): JsonResponse
    {
        return response()->json($menuItem->load('category'));
    }

    public function update(Request $request, MenuItem $menuItem): JsonResponse
    {
        // slug is immutable (shared identity) — not accepted here.
        $data = $this->validateItem($request, creating: false);
        $menuItem->update($data);

        return response()->json($menuItem);
    }

    public function destroy(MenuItem $menuItem, ImageService $images): JsonResponse
    {
        $images->delete($menuItem->category->slug, $menuItem->slug);
        $menuItem->delete();

        return response()->json(null, 204);
    }

    /** Persist a new ordering within a category: body { slugs: [slug, ...] }. */
    public function reorder(Request $request): JsonResponse
    {
        $slugs = $request->validate(['slugs' => 'required|array'])['slugs'];
        foreach ($slugs as $i => $slug) {
            MenuItem::where('slug', $slug)->update(['sort_order' => $i]);
        }

        return response()->json(['ok' => true]);
    }

    public function uploadImage(Request $request, MenuItem $menuItem, ImageService $images): JsonResponse
    {
        $request->validate(['image' => 'required|image|max:5120']);
        $images->store($request->file('image'), $menuItem->category->slug, $menuItem->slug);
        $menuItem->update(['image_updated_at' => now()]);

        return response()->json($menuItem->fresh());
    }

    /** @return array<string, mixed> */
    private function validateItem(Request $request, bool $creating = true): array
    {
        $rules = [
            'category_id' => ($creating ? 'required|' : '').'exists:categories,id',
            'name' => ($creating ? 'required|' : '').'string|max:150',
            'slug' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'price_type' => 'nullable|in:single,sized',
            'price' => 'nullable|integer|min:0',
            'prices' => 'nullable|array',
            'pizza_selection' => 'nullable|array',
            'deal_extras' => 'nullable|array',
            'default_size' => 'nullable|string|max:50',
            'tag' => 'nullable|string|max:50',
            'is_special' => 'boolean',
            'is_signature' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];

        $data = $request->validate($rules);

        // slug is only honoured on create; never changes afterwards.
        if (! $creating) {
            unset($data['slug']);
        }

        return $data;
    }
}
