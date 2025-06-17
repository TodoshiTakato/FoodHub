<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="FoodHub API",
 *     version="1.0.0",
 *     description="Multi-channel SaaS platform for restaurants - API Documentation",
 *     @OA\Contact(
 *         email="admin@foodhub.uz"
 *     )
 * )
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="FoodHub API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="passport",
 *     type="oauth2",
 *     description="Laravel Passport OAuth2 security",
 *     @OA\Flow(
 *         flow="password",
 *         tokenUrl="/oauth/token",
 *         scopes={}
 *     )
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter token in format: Bearer {your-token}"
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication and profile management"
 * )
 * 
 * @OA\Tag(
 *     name="Restaurants",
 *     description="Restaurant management and information"
 * )
 * 
 * @OA\Tag(
 *     name="Menus", 
 *     description="Menu management with multi-channel support"
 * )
 * 
 * @OA\Tag(
 *     name="Products",
 *     description="Product catalog with multilingual support"
 * )
 * 
 * @OA\Tag(
 *     name="Orders",
 *     description="Order management and tracking"
 * )
 */
abstract class Controller
{
    //
}
