<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Restaurant;
use App\Enums\StatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/users",
     *     summary="Get list of users",
     *     tags={"User Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurant_id",
     *         in="query",
     *         description="Filter by restaurant ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filter by role",
     *         required=false,
     *         @OA\Schema(type="string", example="restaurant-manager")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active","inactive","suspended"}, example="active")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name or email",
     *         required=false,
     *         @OA\Schema(type="string", example="john")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Users retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Users retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", example="john@example.com"),
     *                         @OA\Property(property="phone", type="string", example="+998901234567"),
     *                         @OA\Property(property="status", type="string", example="active"),
     *                         @OA\Property(property="restaurant", type="object"),
     *                         @OA\Property(property="roles", type="array", @OA\Items())
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['restaurant:id,name,slug', 'roles:id,name'])
                    ->orderBy('created_at', 'desc');

        // Filter by restaurant
        if ($request->has('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        // Filter by role
        if ($request->has('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $statusValue = StatusEnum::fromString($request->status)?->value;
            if ($statusValue !== null) {
                $query->where('status', $statusValue);
            }
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $users,
            'message' => 'Users retrieved successfully'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users",
     *     summary="Create a new user",
     *     tags={"User Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","role"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="phone", type="string", example="+998901234567"),
     *             @OA\Property(property="restaurant_id", type="integer", example=1),
     *             @OA\Property(property="role", type="string", example="restaurant-manager"),
     *             @OA\Property(property="status", type="string", enum={"active","inactive","suspended"}, example="active")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User created successfully"),
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
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'restaurant_id' => 'nullable|exists:restaurants,id',
            'role' => 'required|exists:roles,name',
            'status' => 'sometimes|in:' . implode(',', StatusEnum::strings()),
        ]);

        $statusValue = StatusEnum::ACTIVE->value; // Default active
        if (isset($validated['status'])) {
            $statusValue = StatusEnum::fromString($validated['status'])?->value ?? StatusEnum::ACTIVE->value;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'restaurant_id' => $validated['restaurant_id'] ?? null,
            'status' => $statusValue,
        ]);

        // Assign role
        $user->assignRole($validated['role']);

        return response()->json([
            'success' => true,
            'data' => $user->load(['restaurant:id,name,slug', 'roles:id,name', 'roles.permissions:id,name']),
            'message' => 'User created successfully'
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/{id}",
     *     summary="Get user details",
     *     tags={"User Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="phone", type="string", example="+998901234567"),
     *                 @OA\Property(property="status", type="string", example="active"),
     *                 @OA\Property(property="restaurant", type="object"),
     *                 @OA\Property(property="roles", type="array", @OA\Items()),
     *                 @OA\Property(property="permissions", type="array", @OA\Items())
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function show(User $user): JsonResponse
    {
        $user->load([
            'restaurant:id,name,slug,currency',
            'roles:id,name',
            'roles.permissions:id,name'
        ]);

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'User retrieved successfully'
        ]);
    }

    /**
     * Update user information
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'restaurant_id' => 'nullable|exists:restaurants,id',
            'status' => 'sometimes|in:' . implode(',', StatusEnum::strings()),
        ]);

        if (isset($validated['status'])) {
            $validated['status'] = StatusEnum::fromString($validated['status'])?->value ?? $user->status;
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'data' => $user->fresh(['restaurant:id,name,slug', 'roles:id,name', 'roles.permissions:id,name']),
            'message' => 'User updated successfully'
        ]);
    }

    /**
     * Change user password (Admin only)
     */
    public function changePassword(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User password changed successfully'
        ]);
    }

    /**
     * Update user roles
     */
    public function updateRoles(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        // Sync roles (remove old, add new)
        $user->syncRoles($validated['roles']);

        return response()->json([
            'success' => true,
            'data' => $user->fresh(['restaurant:id,name,slug', 'roles:id,name', 'roles.permissions:id,name']),
            'message' => 'User roles updated successfully'
        ]);
    }

    /**
     * Suspend/Activate user
     */
    public function updateStatus(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', StatusEnum::strings()),
            'reason' => 'nullable|string|max:500',
        ]);

        $statusValue = StatusEnum::fromString($validated['status'])?->value;
        
        $user->update([
            'status' => $statusValue,
            'suspension_reason' => $validated['reason'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $user->fresh(),
            'message' => 'User status updated successfully'
        ]);
    }

    /**
     * Delete user (Soft delete)
     */
    public function destroy(User $user): JsonResponse
    {
        // Prevent deleting super admin
        if ($user->hasRole('super-admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete super admin user'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Get available roles and permissions
     */
    public function getRolesAndPermissions(): JsonResponse
    {
        $roles = Role::with('permissions:id,name')->get(['id', 'name']);
        
        return response()->json([
            'success' => true,
            'data' => [
                'roles' => $roles,
                'role_descriptions' => [
                    'super-admin' => 'Full system access - manages everything',
                    'restaurant-owner' => 'Owns restaurant - full restaurant management',
                    'restaurant-manager' => 'Manages restaurant operations and staff',
                    'kitchen-staff' => 'Kitchen operations - view orders and menu',
                    'cashier' => 'Handle orders and payments',
                    'call-center-operator' => 'Take phone orders and manage customers',
                    'courier' => 'Delivery staff - update order statuses',
                    'customer' => 'Regular customer - place orders'
                ]
            ],
            'message' => 'Roles and permissions retrieved successfully'
        ]);
    }

    /**
     * Get users by restaurant (for restaurant owners/managers)
     */
    public function getByRestaurant(Restaurant $restaurant, Request $request): JsonResponse
    {
        $users = User::where('restaurant_id', $restaurant->id)
                    ->with(['roles:id,name'])
                    ->orderBy('created_at', 'desc')
                    ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => [
                'restaurant' => $restaurant->only(['id', 'name', 'slug']),
                'users' => $users
            ],
            'message' => 'Restaurant users retrieved successfully'
        ]);
    }
}