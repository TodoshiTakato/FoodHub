<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\RestaurantController;
use App\Http\Controllers\Api\V1\MenuController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\DashboardController;

// API Info
Route::get('/', function () {
    return response()->json([
        'success' => true,
        'data' => [
            'name' => 'FoodHub API',
            'version' => 'v1',
            'description' => 'Multi-channel SaaS platform for restaurants',
            'endpoints' => [
                'auth' => '/auth/*',
                'restaurants' => '/restaurants',
                'categories' => '/categories',
                'menus' => '/menus',
                'products' => '/products',
                'orders' => '/orders',
                'users' => '/users',
                'dashboard' => '/dashboard/*',
            ]
        ],
        'message' => 'Welcome to FoodHub API v1'
    ]);
});

// Authentication routes (public)
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    
    // Protected auth routes
    Route::middleware('auth:apiV1')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::put('password', [AuthController::class, 'changePassword']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
});

// Public restaurant routes
Route::prefix('restaurants')->group(function () {
    Route::get('/', [RestaurantController::class, 'index']);
    Route::get('{restaurant:slug}', [RestaurantController::class, 'show']);
    
    // Restaurant menus, categories and products (public)
    Route::get('{restaurant:slug}/categories', [CategoryController::class, 'getByRestaurant']);
    Route::get('{restaurant:slug}/menus', [MenuController::class, 'getByRestaurant']);
    Route::get('{restaurant:slug}/products', [ProductController::class, 'getByRestaurant']);
    Route::get('{restaurant:slug}/orders', [OrderController::class, 'getByRestaurant'])
        ->middleware('auth:apiV1');
    Route::get('{restaurant:slug}/users', [UserController::class, 'getByRestaurant'])
        ->middleware('auth:apiV1');
});

// Public menu routes
Route::prefix('menus')->group(function () {
    Route::get('/', [MenuController::class, 'index']);
    Route::get('{menu}', [MenuController::class, 'show']);
});

// Public category routes
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('{category}', [CategoryController::class, 'show']);
});

// Public product routes  
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('{product}', [ProductController::class, 'show']);
    Route::get('category/{category}', [ProductController::class, 'getByCategory']);
});

// Protected routes (require authentication only - role checks in controllers)
Route::middleware('auth:apiV1')->group(function () {
    
    // Restaurant management
    Route::prefix('restaurants')->group(function () {
        Route::post('/', [RestaurantController::class, 'store']);
        Route::put('{restaurant}', [RestaurantController::class, 'update']);
        Route::delete('{restaurant}', [RestaurantController::class, 'destroy']);
    });
    
    // Category management
    Route::prefix('categories')->group(function () {
        Route::post('/', [CategoryController::class, 'store']);
        Route::put('{category}', [CategoryController::class, 'update']);
        Route::delete('{category}', [CategoryController::class, 'destroy']);
    });
    
    // Menu management
    Route::prefix('menus')->group(function () {
        Route::post('/', [MenuController::class, 'store']);
        Route::put('{menu}', [MenuController::class, 'update']);
        Route::delete('{menu}', [MenuController::class, 'destroy']);
    });
    
    // Product management
    Route::prefix('products')->group(function () {
        Route::post('/', [ProductController::class, 'store']);
        Route::put('{product}', [ProductController::class, 'update']);
        Route::delete('{product}', [ProductController::class, 'destroy']);
    });
    
    // Order management
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::post('/', [OrderController::class, 'store']);
        Route::get('{order}', [OrderController::class, 'show']);
        Route::put('{order}/status', [OrderController::class, 'updateStatus']);
        Route::post('{order}/cancel', [OrderController::class, 'cancel']);
    });

    // User management
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('roles-permissions', [UserController::class, 'getRolesAndPermissions']);
        Route::get('{user}', [UserController::class, 'show']);
        Route::put('{user}', [UserController::class, 'update']);
        Route::put('{user}/password', [UserController::class, 'changePassword']);
        Route::put('{user}/roles', [UserController::class, 'updateRoles']);
        Route::put('{user}/status', [UserController::class, 'updateStatus']);
        Route::delete('{user}', [UserController::class, 'destroy']);
    });

    // Dashboard analytics
    Route::prefix('dashboard')->group(function () {
        Route::get('stats', [DashboardController::class, 'stats']);
        Route::get('revenue', [DashboardController::class, 'revenue']);
        Route::get('orders', [DashboardController::class, 'orders']);
        Route::get('top-products', [DashboardController::class, 'topProducts']);
    });
});
