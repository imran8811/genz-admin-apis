<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Category::withCount('menuItems')->orderBy('sort_order')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:categories,slug',
            'type' => 'in:single,sized',
            'sizes' => 'nullable|array',
            'is_coming_soon' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        // Slug is the stable, immutable identity. Derive it once at creation.
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        return response()->json(Category::create($data), 201);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json($category->load(['menuItems' => fn ($q) => $q->orderBy('sort_order')]));
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        // Note: slug is intentionally not updatable — it is the shared contract.
        $data = $request->validate([
            'name' => 'string|max:100',
            'type' => 'in:single,sized',
            'sizes' => 'nullable|array',
            'is_coming_soon' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);
        $category->update($data);

        return response()->json($category);
    }

    public function destroy(Category $category, ImageService $images): JsonResponse
    {
        $images->delete($category->slug, $category->slug);
        $category->delete();

        return response()->json(null, 204);
    }

    /** Persist a new ordering: body { slugs: [slug, ...] }. */
    public function reorder(Request $request): JsonResponse
    {
        $slugs = $request->validate(['slugs' => 'required|array'])['slugs'];
        foreach ($slugs as $i => $slug) {
            Category::where('slug', $slug)->update(['sort_order' => $i]);
        }

        return response()->json(['ok' => true]);
    }

    public function uploadImage(Request $request, Category $category, ImageService $images): JsonResponse
    {
        $request->validate(['image' => 'required|image|max:5120']);
        $images->store($request->file('image'), $category->slug, $category->slug);
        $category->update(['image_updated_at' => now()]);

        return response()->json($category);
    }
}
