<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/categories",
     *     summary="Get list of categories",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="restaurant_id",
     *         in="query",
     *         description="Filter by restaurant ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categories retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Categories retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="object", example={"en":"Pizza","ru":"Пицца","uz":"Pizza"}),
     *                         @OA\Property(property="slug", type="string", example="pizza"),
     *                         @OA\Property(property="description", type="object"),
     *                         @OA\Property(property="restaurant", type="object"),
     *                         @OA\Property(property="products_count", type="integer", example=5)
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::with(['restaurant:id,name,slug'])
                        ->withCount('products')
                        ->active();

        // Filter by restaurant
        if ($request->has('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        $categories = $query->orderBy('sort_order')
                           ->orderByRaw("name->>'en'")
                           ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $categories,
            'message' => 'Categories retrieved successfully'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/categories",
     *     summary="Create a new category",
     *     tags={"Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"restaurant_id","name"},
     *             @OA\Property(property="restaurant_id", type="integer", example=1),
     *             @OA\Property(property="name", type="object",
     *                 required={"en","ru","uz"},
     *                 @OA\Property(property="en", type="string", example="Pizza"),
     *                 @OA\Property(property="ru", type="string", example="Пицца"),
     *                 @OA\Property(property="uz", type="string", example="Pizza")
     *             ),
     *             @OA\Property(property="description", type="object",
     *                 @OA\Property(property="en", type="string", example="Fresh baked pizza"),
     *                 @OA\Property(property="ru", type="string", example="Свежая выпечка пиццы"),
     *                 @OA\Property(property="uz", type="string", example="Yangi pishirilgan pizza")
     *             ),
     *             @OA\Property(property="image_url", type="string", format="url", example="https://example.com/pizza.jpg"),
     *             @OA\Property(property="sort_order", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'name' => 'required|array',
            'name.en' => 'required|string|max:255',
            'name.ru' => 'required|string|max:255',
            'name.uz' => 'required|string|max:255',
            'description' => 'nullable|array',
            'description.en' => 'nullable|string|max:1000',
            'description.ru' => 'nullable|string|max:1000',
            'description.uz' => 'nullable|string|max:1000',
            'image_url' => 'nullable|url',
            'sort_order' => 'nullable|integer|min:0',
            'settings' => 'nullable|array',
        ]);

        $validated['slug'] = \Str::slug($validated['name']['en']);
        $validated['is_active'] = true;

        $category = Category::create($validated);

        return response()->json([
            'success' => true,
            'data' => $category->load('restaurant:id,name,slug'),
            'message' => 'Category created successfully'
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/categories/{id}",
     *     summary="Get category details",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Category ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="object"),
     *                 @OA\Property(property="description", type="object"),
     *                 @OA\Property(property="restaurant", type="object"),
     *                 @OA\Property(property="products", type="array", @OA\Items())
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     )
     * )
     */
    public function show(Category $category): JsonResponse
    {
        $category->load([
            'restaurant:id,name,slug,currency',
            'products' => function($query) {
                $query->active()
                      ->inStock()
                      ->select(['id', 'category_id', 'name', 'prices', 'images', 'is_featured'])
                      ->orderBy('sort_order')
                      ->orderByRaw("name->>'en'");
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => $category,
            'message' => 'Category retrieved successfully'
        ]);
    }

    /**
     * Update category
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|array',
            'name.en' => 'required_with:name|string|max:255',
            'name.ru' => 'required_with:name|string|max:255',
            'name.uz' => 'required_with:name|string|max:255',
            'description' => 'nullable|array',
            'description.en' => 'nullable|string|max:1000',
            'description.ru' => 'nullable|string|max:1000',
            'description.uz' => 'nullable|string|max:1000',
            'image_url' => 'nullable|url',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'settings' => 'nullable|array',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = \Str::slug($validated['name']['en']);
        }

        $category->update($validated);

        return response()->json([
            'success' => true,
            'data' => $category->fresh(['restaurant:id,name,slug']),
            'message' => 'Category updated successfully'
        ]);
    }

    /**
     * Delete category
     */
    public function destroy(Category $category): JsonResponse
    {
        // Check if category has products
        if ($category->products()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category that has products'
            ], 400);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }

    /**
     * Get categories by restaurant
     */
    public function getByRestaurant(Restaurant $restaurant, Request $request): JsonResponse
    {
        $categories = Category::where('restaurant_id', $restaurant->id)
                             ->active()
                             ->withCount('products')
                             ->orderBy('sort_order')
                             ->orderByRaw("name->>'en'")
                             ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'restaurant' => $restaurant->only(['id', 'name', 'slug']),
                'categories' => $categories
            ],
            'message' => 'Restaurant categories retrieved successfully'
        ]);
    }
} 