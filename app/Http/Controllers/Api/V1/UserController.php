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
        // Проверяем права доступа - добавляем admin
        if (!$request->user()->hasAnyRole(['super-admin', 'admin', 'restaurant-owner', 'restaurant-manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions to view users'
            ], 403);
        }

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
     *             @OA\Property(property="role", type="string", example="restaurant-manager", description="super-admin: ALL roles | admin: restaurant-owner+ | restaurant-owner: staff only"),
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
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - cannot assign this role"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        // Проверяем права доступа - добавляем admin
        if (!$request->user()->hasAnyRole(['super-admin', 'admin', 'restaurant-owner'])) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions to create users'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'restaurant_id' => 'nullable|exists:restaurants,id',
            'role' => 'required|exists:roles,name',
            'status' => 'sometimes|in:' . implode(',', StatusEnum::strings()),
        ]);

        // 🔥 НОВОЕ: Разрешаем super-admin создавать других super-admin
        if ($validated['role'] === 'super-admin') {
            if (!$request->user()->hasRole('super-admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only super-admin can create other super-admin users'
                ], 403);
            }
        }

        // 🔒 ЗАЩИТА: Admin могут создавать только определенные роли  
        if ($request->user()->hasRole('admin') && !$request->user()->hasRole('super-admin')) {
            $allowedRoles = ['restaurant-owner', 'restaurant-manager', 'kitchen-staff', 'cashier', 'call-center-operator', 'courier', 'customer'];
            if (!in_array($validated['role'], $allowedRoles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin users can only create: ' . implode(', ', $allowedRoles)
                ], 403);
            }
        }

        // 🔒 ЗАЩИТА: Restaurant owners могут создавать только определенные роли
        if ($request->user()->hasRole('restaurant-owner') && !$request->user()->hasAnyRole(['super-admin', 'admin'])) {
            $allowedRoles = ['restaurant-manager', 'kitchen-staff', 'cashier', 'call-center-operator', 'courier', 'customer'];
            if (!in_array($validated['role'], $allowedRoles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Restaurant owners can only create: ' . implode(', ', $allowedRoles)
                ], 403);
            }
        }

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
        // Проверяем права доступа - добавляем admin
        if (!$request->user()->hasAnyRole(['super-admin', 'admin', 'restaurant-owner'])) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions to update users'
            ], 403);
        }

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
     * @OA\Put(
     *     path="/api/v1/users/{id}/password",
     *     summary="Change user password (admin only)",
     *     tags={"User Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"password", "password_confirmation"},
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User password changed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User password changed successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Insufficient permissions"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function changePassword(Request $request, User $user): JsonResponse
    {
        // Проверяем права доступа - добавляем admin
        if (!$request->user()->hasAnyRole(['super-admin', 'admin', 'restaurant-owner'])) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions to change user passwords'
            ], 403);
        }

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
     * @OA\Put(
     *     path="/api/v1/users/{id}/roles",
     *     summary="Update user roles (super-admin only)",
     *     tags={"User Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"roles"},
     *             @OA\Property(property="roles", type="array", @OA\Items(type="string", example="restaurant-manager"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User roles updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User roles updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Cannot change super admin roles"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Only super admin can change user roles"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function updateRoles(Request $request, User $user): JsonResponse
    {
        // Только super-admin может менять роли (admin не может менять роли)
        if (!$request->user()->hasRole('super-admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Only super admin can change user roles'
            ], 403);
        }

        // Prevent changing super admin roles
        if ($user->hasRole('super-admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot change super admin roles'
            ], 400);
        }

        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        // 🔥 НОВОЕ: Разрешаем назначение любых ролей включая super-admin
        // Только super-admin может назначать super-admin роль
        if (in_array('super-admin', $validated['roles'])) {
            // Double-check: только super-admin может назначать super-admin
            if (!$request->user()->hasRole('super-admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only super-admin can assign super-admin role'
                ], 403);
            }
        }

        // Sync roles (remove old, add new)
        $user->syncRoles($validated['roles']);

        return response()->json([
            'success' => true,
            'data' => $user->fresh(['restaurant:id,name,slug', 'roles:id,name', 'roles.permissions:id,name']),
            'message' => 'User roles updated successfully'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/users/{id}/status",
     *     summary="Update user status (admin/owner only)",
     *     tags={"User Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"active","inactive","suspended"}, example="suspended"),
     *             @OA\Property(property="reason", type="string", example="Violation of company policy")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User status updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Cannot change super admin status"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Insufficient permissions to change user status"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function updateStatus(Request $request, User $user): JsonResponse
    {
        // Проверяем права доступа - добавляем admin
        if (!$request->user()->hasAnyRole(['super-admin', 'admin', 'restaurant-owner'])) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions to change user status'
            ], 403);
        }

        // Prevent suspending super admin
        if ($user->hasRole('super-admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot change super admin status'
            ], 400);
        }

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
        // Super-admin и admin могут удалять пользователей
        if (!auth()->user()->hasAnyRole(['super-admin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only super admin or admin can delete users'
            ], 403);
        }

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
     * @OA\Get(
     *     path="/api/v1/users/roles-permissions",
     *     summary="Get available roles and permissions",
     *     tags={"User Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Available roles retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Available roles retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Insufficient permissions to view roles"
     *     )
     * )
     */
    public function getRolesAndPermissions(Request $request): JsonResponse
    {
        // Определяем доступные роли в зависимости от прав пользователя
        $availableRoles = [];
        
        if ($request->user()->hasRole('super-admin')) {
            // 🔥 НОВОЕ: Super-admin видит ВСЕ роли включая super-admin (для управления)
            $availableRoles = Role::with('permissions:id,name')
                                 ->get(['id', 'name']);
        } elseif ($request->user()->hasRole('admin')) {
            // Admin могут назначать роли кроме super-admin и admin
            $allowedRoleNames = ['restaurant-owner', 'restaurant-manager', 'kitchen-staff', 'cashier', 'call-center-operator', 'courier', 'customer'];
            $availableRoles = Role::whereIn('name', $allowedRoleNames)
                                 ->with('permissions:id,name')
                                 ->get(['id', 'name']);
        } elseif ($request->user()->hasRole('restaurant-owner')) {
            // Restaurant owners могут назначать только определенные роли
            $allowedRoleNames = ['restaurant-manager', 'kitchen-staff', 'cashier', 'call-center-operator', 'courier', 'customer'];
            $availableRoles = Role::whereIn('name', $allowedRoleNames)
                                 ->with('permissions:id,name')
                                 ->get(['id', 'name']);
        } else {
            // Остальные пользователи не могут создавать пользователей
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions to view roles'
            ], 403);
        }

        $roleDescriptions = [
            'super-admin' => '🔥 SYSTEM GOD - Full system access (CAN be created via API by super-admin)',
            'admin' => '⚡ ADMIN - System administration (manages restaurants and owners)',
            'restaurant-owner' => '👑 Restaurant Owner - Full restaurant management',
            'restaurant-manager' => '👨‍💼 Restaurant Manager - Operations and staff management',
            'kitchen-staff' => '👨‍🍳 Kitchen Staff - Kitchen operations and orders',
            'cashier' => '💰 Cashier - Handle orders and payments',
            'call-center-operator' => '📞 Call Center - Phone orders and customer service',
            'courier' => '🚗 Courier - Delivery staff and order status updates',
            'customer' => '🙋‍♂️ Customer - Regular customer ordering'
        ];

        // Фильтруем описания только для доступных ролей
        $filteredDescriptions = [];
        foreach ($availableRoles as $role) {
            if (isset($roleDescriptions[$role->name])) {
                $filteredDescriptions[$role->name] = $roleDescriptions[$role->name];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'available_roles' => $availableRoles,
                'role_descriptions' => $filteredDescriptions,
                'restrictions' => [
                    'super_admin_note' => 'Super-admin users can be created via API by other super-admin users',
                    'admin_note' => 'Admin users can manage restaurants and owners but cannot create super-admin or admin',
                    'restaurant_owner_limit' => 'Restaurant owners can only create staff roles',
                    'current_user_role' => $request->user()->roles->pluck('name')->first(),
                    'role_hierarchy' => [
                        'super-admin' => 'Can create: ALL roles including super-admin',
                        'admin' => 'Can create: restaurant-owner and below',
                        'restaurant-owner' => 'Can create: staff only'
                    ]
                ]
            ],
            'message' => 'Available roles retrieved successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/restaurants/{restaurant}/users",
     *     summary="Get users by restaurant",
     *     tags={"User Management"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurant",
     *         in="path",
     *         description="Restaurant ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Restaurant users retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Restaurant users retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
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