<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/products",
     *     summary="Get list of products",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="restaurant_id",
     *         in="query",
     *         description="Filter by restaurant ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter by category ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="channel",
     *         in="query",
     *         description="Filter by channel",
     *         required=false,
     *         @OA\Schema(type="string", enum={"web","mobile","telegram","whatsapp","phone","pos"}, example="web")
     *     ),
     *     @OA\Parameter(
     *         name="featured",
     *         in="query",
     *         description="Show only featured products",
     *         required=false,
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by product name or SKU",
     *         required=false,
     *         @OA\Schema(type="string", example="pizza")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Products retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Products retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="object", example={"en":"Margherita Pizza","ru":"Пицца Маргарита","uz":"Margarita Pizza"}),
     *                         @OA\Property(property="prices", type="object", example={"web":12.99,"mobile":12.99}),
     *                         @OA\Property(property="restaurant", type="object"),
     *                         @OA\Property(property="category", type="object")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['restaurant:id,name,slug', 'category:id,name'])
                       ->active()
                       ->inStock();

        // Filter by restaurant
        if ($request->has('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by channel
        if ($request->has('channel')) {
            $query->forChannel($request->channel);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter featured products
        if ($request->boolean('featured')) {
            $query->featured();
        }

        // Search by name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(name, '$.ru') LIKE ?", ["%{$search}%"])
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('sort_order')
                         ->orderByRaw("name->>'en'")
                         ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $products,
            'message' => 'Products retrieved successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/restaurants/{restaurant_slug}/products",
     *     summary="Get products for specific restaurant",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="restaurant_slug",
     *         in="path",
     *         description="Restaurant slug",
     *         required=true,
     *         @OA\Schema(type="string", example="pizza-palace")
     *     ),
     *     @OA\Parameter(
     *         name="channel",
     *         in="query",
     *         description="Filter by channel",
     *         required=false,
     *         @OA\Schema(type="string", enum={"web","mobile","telegram","whatsapp","phone","pos"}, example="web")
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter by category ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Restaurant products retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Restaurant products retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="restaurant", type="object"),
     *                 @OA\Property(property="products", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function getByRestaurant(Restaurant $restaurant, Request $request): JsonResponse
    {
        $channel = $request->get('channel', 'web');
        
        $query = Product::where('restaurant_id', $restaurant->id)
                       ->active()
                       ->inStock()
                       ->forChannel($channel)
                       ->with('category:id,name');

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter featured products
        if ($request->boolean('featured')) {
            $query->featured();
        }

        $products = $query->orderBy('sort_order')
                         ->orderByRaw("name->>'en'")
                         ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => [
                'restaurant' => $restaurant->only(['id', 'name', 'slug', 'currency']),
                'products' => $products
            ],
            'message' => 'Restaurant products retrieved successfully'
        ]);
    }

    /**
     * Get products by category
     */
    public function getByCategory(Category $category, Request $request): JsonResponse
    {
        $channel = $request->get('channel', 'web');
        
        $products = Product::where('category_id', $category->id)
                          ->active()
                          ->inStock()
                          ->forChannel($channel)
                          ->with(['restaurant:id,name,slug,currency'])
                          ->orderBy('sort_order')
                          ->orderByRaw("name->>'en'")
                          ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => [
                'category' => $category->only(['id', 'name', 'slug']),
                'products' => $products
            ],
            'message' => 'Category products retrieved successfully'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/products",
     *     summary="Create a new product",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"restaurant_id","category_id","name","type","prices","channels"},
     *             @OA\Property(property="restaurant_id", type="integer", example=1),
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="name", type="object", 
     *                 required={"en","ru","uz"},
     *                 @OA\Property(property="en", type="string", example="Margherita Pizza"),
     *                 @OA\Property(property="ru", type="string", example="Пицца Маргарита"),
     *                 @OA\Property(property="uz", type="string", example="Margarita Pizza")
     *             ),
     *             @OA\Property(property="description", type="object",
     *                 @OA\Property(property="en", type="string", example="Classic pizza with tomato and mozzarella"),
     *                 @OA\Property(property="ru", type="string", example="Классическая пицца с томатами и моцареллой"),
     *                 @OA\Property(property="uz", type="string", example="Pomidor va mozzarella bilan klassik pizza")
     *             ),
     *             @OA\Property(property="type", type="string", enum={"simple","combo","modifier"}, example="simple"),
     *             @OA\Property(property="prices", type="object", example={"web":12.99,"mobile":12.99,"pos":11.99}),
     *             @OA\Property(property="channels", type="array", @OA\Items(type="string", enum={"web","mobile","telegram","whatsapp","phone","pos"})),
     *             @OA\Property(property="sku", type="string", example="PIZZA-001"),
     *             @OA\Property(property="images", type="array", @OA\Items(type="string", format="url")),
     *             @OA\Property(property="calories", type="integer", example=250),
     *             @OA\Property(property="allergens", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="ingredients", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product created successfully"),
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
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|array',
            'name.en' => 'required|string|max:255', // Обязательный английский
            'name.ru' => 'required|string|max:255', // Обязательный русский
            'name.uz' => 'required|string|max:255', // Обязательный узбекский
            'description' => 'nullable|array',
            'description.en' => 'nullable|string|max:2000',
            'description.ru' => 'nullable|string|max:2000', 
            'description.uz' => 'nullable|string|max:2000',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'type' => 'required|in:simple,combo,modifier',
            'images' => 'nullable|array',
            'images.*' => 'url',
            'prices' => 'required|array',
            'prices.*' => 'numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'calories' => 'nullable|integer|min:0',
            'allergens' => 'nullable|array',
            'ingredients' => 'nullable|array',
            'nutritional_info' => 'nullable|array',
            'modifiers' => 'nullable|array',
            'combo_items' => 'nullable|array',
            'channels' => 'required|array',
            'channels.*' => 'in:web,mobile,telegram,whatsapp,phone,pos',
            'availability_hours' => 'nullable|array',
            'sort_order' => 'nullable|integer|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'track_stock' => 'nullable|boolean',
            'settings' => 'nullable|array',
        ]);

        $validated['slug'] = \Str::slug($validated['name']['en'] ?? $validated['name'][array_key_first($validated['name'])]);
        $validated['is_active'] = true;
        $validated['is_featured'] = false;

        $product = Product::create($validated);

        return response()->json([
            'success' => true,
            'data' => $product->load(['restaurant:id,name,slug', 'category:id,name']),
            'message' => 'Product created successfully'
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/products/{id}",
     *     summary="Get product details",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="channel",
     *         in="query",
     *         description="Channel for pricing",
     *         required=false,
     *         @OA\Schema(type="string", enum={"web","mobile","telegram","whatsapp","phone","pos"}, example="web")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="object"),
     *                 @OA\Property(property="description", type="object"),
     *                 @OA\Property(property="current_price", type="number", format="float", example=12.99),
     *                 @OA\Property(property="restaurant", type="object"),
     *                 @OA\Property(property="category", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     )
     * )
     */
    public function show(Product $product, Request $request): JsonResponse
    {
        $channel = $request->get('channel', 'web');
        
        $product->load([
            'restaurant:id,name,slug,currency',
            'category:id,name,slug'
        ]);

        // Add channel-specific price
        $product->current_price = $product->getPriceForChannel($channel);

        return response()->json([
            'success' => true,
            'data' => $product,
            'message' => 'Product retrieved successfully'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name' => 'sometimes|array',
            'name.en' => 'required_with:name|string|max:255',
            'name.ru' => 'required_with:name|string|max:255', 
            'name.uz' => 'required_with:name|string|max:255',
            'description' => 'nullable|array',
            'description.en' => 'nullable|string|max:2000',
            'description.ru' => 'nullable|string|max:2000',
            'description.uz' => 'nullable|string|max:2000',
            'sku' => 'nullable|string|max:100|unique:products,sku,' . $product->id,
            'type' => 'sometimes|in:simple,combo,modifier',
            'images' => 'nullable|array',
            'images.*' => 'url',
            'prices' => 'sometimes|array',
            'prices.*' => 'numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'calories' => 'nullable|integer|min:0',
            'allergens' => 'nullable|array',
            'ingredients' => 'nullable|array',
            'nutritional_info' => 'nullable|array',
            'modifiers' => 'nullable|array',
            'combo_items' => 'nullable|array',
            'channels' => 'sometimes|array',
            'channels.*' => 'in:web,mobile,telegram,whatsapp,phone,pos',
            'availability_hours' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'track_stock' => 'nullable|boolean',
            'settings' => 'nullable|array',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = \Str::slug($validated['name']['en'] ?? $validated['name'][array_key_first($validated['name'])]);
        }

        $product->update($validated);

        return response()->json([
            'success' => true,
            'data' => $product->fresh(['restaurant:id,name,slug', 'category:id,name']),
            'message' => 'Product updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }
} 