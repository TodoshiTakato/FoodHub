<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\RestaurantController;

// Публичные роуты
Route::get('/', function () {
    return response()->json([
        'message' => 'FoodHub API v1',
        'version' => '1.0.0',
        'documentation' => '/api/v1/docs',
        'status' => 'active'
    ]);
});

// Публичные роуты ресторанов (для клиентов)
Route::get('/restaurants', [RestaurantController::class, 'index']);
Route::get('/restaurants/{restaurant:slug}', [RestaurantController::class, 'show']);

// Защищенные роуты (требуют аутентификацию)
Route::middleware('auth:api')->group(function () {
    // Информация о пользователе
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()->load('restaurant'),
            'message' => 'User information retrieved successfully'
        ]);
    });

    // Управление ресторанами (только для владельцев/админов)
    Route::middleware('can:manage-restaurants')->group(function () {
        Route::post('/restaurants', [RestaurantController::class, 'store']);
        Route::put('/restaurants/{restaurant}', [RestaurantController::class, 'update']);
        Route::delete('/restaurants/{restaurant}', [RestaurantController::class, 'destroy']);
    });
});
