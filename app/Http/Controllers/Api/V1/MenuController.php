<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/menus",
     *     summary="Get list of menus",
     *     tags={"Menus"},
     *     @OA\Parameter(
     *         name="restaurant_id",
     *         in="query",
     *         description="Filter by restaurant ID",
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
     *         name="type",
     *         in="query",
     *         description="Filter by menu type",
     *         required=false,
     *         @OA\Schema(type="string", enum={"main","breakfast","lunch","dinner","drinks","desserts","seasonal"}, example="main")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Menus retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Menus retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="object", example={"en":"Main Menu","ru":"Основное меню","uz":"Asosiy menyu"}),
     *                         @OA\Property(property="type", type="string", example="main"),
     *                         @OA\Property(property="channels", type="array", @OA\Items(type="string")),
     *                         @OA\Property(property="restaurant", type="object")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Menu::with(['restaurant:id,name,slug'])
                    ->active();

        // Filter by restaurant
        if ($request->has('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        // Filter by channel
        if ($request->has('channel')) {
            $query->forChannel($request->channel);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $menus = $query->orderBy('sort_order')
                      ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $menus,
            'message' => 'Menus retrieved successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/restaurants/{restaurant_slug}/menus",
     *     summary="Get menus for specific restaurant",
     *     tags={"Menus"},
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
     *     @OA\Response(
     *         response=200,
     *         description="Restaurant menus retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Restaurant menus retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="restaurant", type="object"),
     *                 @OA\Property(property="menus", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="object"),
     *                         @OA\Property(property="products", type="array", @OA\Items())
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getByRestaurant(Restaurant $restaurant, Request $request): JsonResponse
    {
        $channel = $request->get('channel', 'web');
        
        $menus = Menu::where('restaurant_id', $restaurant->id)
                    ->active()
                    ->forChannel($channel)
                    ->with(['products' => function($query) use ($channel) {
                        $query->active()
                              ->inStock()
                              ->forChannel($channel)
                              ->with('category:id,name')
                              ->orderBy('pivot_sort_order');
                    }])
                    ->orderBy('sort_order')
                    ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'restaurant' => $restaurant->only(['id', 'name', 'slug']),
                'menus' => $menus
            ],
            'message' => 'Restaurant menus retrieved successfully'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/menus",
     *     summary="Create a new menu",
     *     tags={"Menus"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"restaurant_id","name","type","channels"},
     *             @OA\Property(property="restaurant_id", type="integer", example=1),
     *             @OA\Property(property="name", type="object", example={"en":"Main Menu","ru":"Основное меню","uz":"Asosiy menyu"}),
     *             @OA\Property(property="description", type="object", example={"en":"Our main menu","ru":"Наше основное меню","uz":"Bizning asosiy menyumiz"}),
     *             @OA\Property(property="type", type="string", enum={"main","breakfast","lunch","dinner","drinks","desserts","seasonal"}, example="main"),
     *             @OA\Property(property="channels", type="array", @OA\Items(type="string", enum={"web","mobile","telegram","whatsapp","phone","pos"})),
     *             @OA\Property(property="availability_hours", type="object"),
     *             @OA\Property(property="sort_order", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Menu created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Menu created successfully"),
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
            'name.*' => 'required|string|max:255',
            'description' => 'nullable|array',
            'description.*' => 'nullable|string|max:1000',
            'type' => 'required|in:main,breakfast,lunch,dinner,drinks,desserts,seasonal',
            'channels' => 'required|array',
            'channels.*' => 'in:web,mobile,telegram,whatsapp,phone,pos',
            'availability_hours' => 'nullable|array',
            'sort_order' => 'nullable|integer|min:0',
            'settings' => 'nullable|array',
        ]);

        $validated['slug'] = \Str::slug($validated['name']['en'] ?? $validated['name'][array_key_first($validated['name'])]);
        $validated['is_active'] = true;

        $menu = Menu::create($validated);

        return response()->json([
            'success' => true,
            'data' => $menu->load('restaurant:id,name,slug'),
            'message' => 'Menu created successfully'
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/menus/{id}",
     *     summary="Get menu details with products",
     *     tags={"Menus"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Menu ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="channel",
     *         in="query",
     *         description="Filter products by channel",
     *         required=false,
     *         @OA\Schema(type="string", enum={"web","mobile","telegram","whatsapp","phone","pos"}, example="web")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Menu retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Menu retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="object"),
     *                 @OA\Property(property="description", type="object"),
     *                 @OA\Property(property="type", type="string", example="main"),
     *                 @OA\Property(property="restaurant", type="object"),
     *                 @OA\Property(property="products", type="array", @OA\Items())
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Menu not found"
     *     )
     * )
     */
    public function show(Menu $menu, Request $request): JsonResponse
    {
        $channel = $request->get('channel', 'web');
        
        $menu->load([
            'restaurant:id,name,slug,currency',
            'products' => function($query) use ($channel) {
                $query->active()
                      ->inStock()
                      ->forChannel($channel)
                      ->with('category:id,name')
                      ->orderBy('pivot_sort_order');
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => $menu,
            'message' => 'Menu retrieved successfully'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Menu $menu): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|array',
            'name.*' => 'required|string|max:255',
            'description' => 'nullable|array',
            'description.*' => 'nullable|string|max:1000',
            'type' => 'sometimes|in:main,breakfast,lunch,dinner,drinks,desserts,seasonal',
            'channels' => 'sometimes|array',
            'channels.*' => 'in:web,mobile,telegram,whatsapp,phone,pos',
            'availability_hours' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'settings' => 'nullable|array',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = \Str::slug($validated['name']['en'] ?? $validated['name'][array_key_first($validated['name'])]);
        }

        $menu->update($validated);

        return response()->json([
            'success' => true,
            'data' => $menu->fresh(['restaurant:id,name,slug']),
            'message' => 'Menu updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Menu $menu): JsonResponse
    {
        $menu->delete();

        return response()->json([
            'success' => true,
            'message' => 'Menu deleted successfully'
        ]);
    }
}
