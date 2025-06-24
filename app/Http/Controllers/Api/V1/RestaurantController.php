<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Enums\StatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RestaurantController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/restaurants",
     *     summary="Get list of restaurants",
     *     tags={"Restaurants"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Restaurants retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Restaurants retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Pizza Palace"),
     *                         @OA\Property(property="slug", type="string", example="pizza-palace"),
     *                         @OA\Property(property="description", type="string", example="Best pizza in town"),
     *                         @OA\Property(property="address", type="object"),
     *                         @OA\Property(property="phone", type="string", example="+998901234567"),
     *                         @OA\Property(property="currency", type="string", example="USD")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $restaurants = Restaurant::active()
            ->verified()
            ->select(['id', 'name', 'slug', 'description', 'address', 'phone', 'business_hours', 'currency'])
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $restaurants,
            'message' => 'Restaurants retrieved successfully'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/restaurants",
     *     summary="Create a new restaurant",
     *     tags={"Restaurants"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Pizza Palace"),
     *             @OA\Property(property="description", type="string", example="Best pizza in town"),
     *             @OA\Property(property="phone", type="string", example="+998901234567"),
     *             @OA\Property(property="email", type="string", format="email", example="info@pizzapalace.uz"),
     *             @OA\Property(property="address", type="object", example={"street":"Main Street 123","city":"Tashkent","country":"Uzbekistan"}),
     *             @OA\Property(property="latitude", type="number", format="float", example=41.2995),
     *             @OA\Property(property="longitude", type="number", format="float", example=69.2401),
     *             @OA\Property(property="currency", type="string", example="USD"),
     *             @OA\Property(property="languages", type="array", @OA\Items(type="string"), example={"en","ru","uz"}),
     *             @OA\Property(property="business_hours", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Restaurant created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Restaurant created successfully"),
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|array',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'timezone' => 'nullable|string|max:50',
            'currency' => 'nullable|string|size:3',
            'languages' => 'nullable|array',
            'business_hours' => 'nullable|array',
        ]);

        $validated['slug'] = \Str::slug($validated['name']);
        
        $restaurant = Restaurant::create($validated);

        return response()->json([
            'success' => true,
            'data' => $restaurant,
            'message' => 'Restaurant created successfully'
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/restaurants/{id}",
     *     summary="Get restaurant details",
     *     tags={"Restaurants"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Restaurant ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Restaurant retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Restaurant retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Pizza Palace"),
     *                 @OA\Property(property="slug", type="string", example="pizza-palace"),
     *                 @OA\Property(property="description", type="string", example="Best pizza in town"),
     *                 @OA\Property(property="address", type="object"),
     *                 @OA\Property(property="phone", type="string", example="+998901234567"),
     *                 @OA\Property(property="business_hours", type="object"),
     *                 @OA\Property(property="currency", type="string", example="USD")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Restaurant not found"
     *     )
     * )
     */
    public function show(Restaurant $restaurant): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $restaurant,
            'message' => 'Restaurant retrieved successfully'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Restaurant $restaurant): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|array',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'timezone' => 'nullable|string|max:50',
            'currency' => 'nullable|string|size:3',
            'languages' => 'nullable|array',
            'business_hours' => 'nullable|array',
            'status' => 'sometimes|in:' . implode(',', StatusEnum::strings()),
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = \Str::slug($validated['name']);
        }

        $restaurant->update($validated);

        return response()->json([
            'success' => true,
            'data' => $restaurant->fresh(),
            'message' => 'Restaurant updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Restaurant $restaurant): JsonResponse
    {
        $restaurant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Restaurant deleted successfully'
        ]);
    }
}
