<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/orders",
     *     summary="Get list of orders",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="restaurant_id",
     *         in="query",
     *         description="Filter by restaurant ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by order status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending","confirmed","preparing","ready","out_for_delivery","delivered","cancelled"}, example="pending")
     *     ),
     *     @OA\Parameter(
     *         name="channel",
     *         in="query",
     *         description="Filter by channel",
     *         required=false,
     *         @OA\Schema(type="string", enum={"web","mobile","telegram","whatsapp","phone","pos"}, example="web")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Filter orders from date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Filter orders to date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2024-12-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Orders retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Orders retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="order_number", type="string", example="PIZ-000001"),
     *                         @OA\Property(property="status", type="string", example="pending"),
     *                         @OA\Property(property="total_amount", type="string", example="23.98"),
     *                         @OA\Property(property="restaurant", type="object"),
     *                         @OA\Property(property="user", type="object")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['restaurant:id,name,slug', 'user:id,name,email', 'items.product:id,name'])
                     ->orderBy('created_at', 'desc');

        // Filter by restaurant
        if ($request->has('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        // Filter by channel
        if ($request->has('channel')) {
            $query->byChannel($request->channel);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $orders,
            'message' => 'Orders retrieved successfully'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/orders",
     *     summary="Create a new order",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"restaurant_id","channel","customer_info","delivery_info","items"},
     *             @OA\Property(property="restaurant_id", type="integer", example=1),
     *             @OA\Property(property="channel", type="string", enum={"web","mobile","telegram","whatsapp","phone","pos"}, example="web"),
     *             @OA\Property(property="customer_info", type="object",
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="phone", type="string", example="+998901234567"),
     *                 @OA\Property(property="email", type="string", example="john@example.com")
     *             ),
     *             @OA\Property(property="delivery_info", type="object",
     *                 @OA\Property(property="type", type="string", enum={"delivery","pickup"}, example="pickup")
     *             ),
     *             @OA\Property(property="items", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="product_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="integer", example=2),
     *                     @OA\Property(property="modifiers", type="array", @OA\Items()),
     *                     @OA\Property(property="special_instructions", type="string", example="No onions")
     *                 )
     *             ),
     *             @OA\Property(property="notes", type="string", example="Please call when ready")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="order_number", type="string", example="PIZ-000001"),
     *                 @OA\Property(property="status", type="string", example="pending"),
     *                 @OA\Property(property="total_amount", type="string", example="23.98"),
     *                 @OA\Property(property="currency", type="string", example="USD")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
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
            'channel' => 'required|in:web,mobile,telegram,whatsapp,phone,pos',
            'customer_info' => 'required|array',
            'customer_info.name' => 'required|string|max:255',
            'customer_info.phone' => 'required|string|max:20',
            'customer_info.email' => 'nullable|email|max:255',
            'delivery_info' => 'required|array',
            'delivery_info.type' => 'required|in:delivery,pickup',
            'delivery_info.address' => 'required_if:delivery_info.type,delivery|array',
            'delivery_info.scheduled_at' => 'nullable|date|after:now',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.modifiers' => 'nullable|array',
            'items.*.special_instructions' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'special_instructions' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $restaurant = Restaurant::findOrFail($validated['restaurant_id']);
            
            // Calculate order totals
            $itemsTotal = 0;
            $orderItems = [];

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                // Check if product is available for this channel
                if (!in_array($validated['channel'], $product->channels)) {
                    throw new \Exception("Product {$product->name} is not available for {$validated['channel']} channel");
                }

                $unitPrice = $product->getPriceForChannel($validated['channel']);
                $modifiersPrice = 0;

                if (isset($item['modifiers'])) {
                    foreach ($item['modifiers'] as $modifier) {
                        $modifiersPrice += $modifier['price'] ?? 0;
                    }
                }

                $totalPrice = ($unitPrice + $modifiersPrice) * $item['quantity'];
                $itemsTotal += $totalPrice;

                // Сохраняем полное название продукта для истории заказа
                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name, // Сохраняем весь JSON
                    'product_sku' => $product->sku,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'modifiers' => json_encode($item['modifiers'] ?? []),
                    'special_instructions' => $item['special_instructions'] ?? null,
                ];
            }

            // Calculate fees and taxes
            $taxRate = $restaurant->settings['tax_rate'] ?? 0;
            $taxAmount = $itemsTotal * ($taxRate / 100);
            
            $deliveryFee = 0;
            if ($validated['delivery_info']['type'] === 'delivery') {
                $deliveryFee = $restaurant->settings['delivery_fee'] ?? 0;
            }

            $serviceFee = $restaurant->settings['service_fee'] ?? 0;
            $totalAmount = $itemsTotal + $taxAmount + $deliveryFee + $serviceFee;

            // Create order
            $order = Order::create([
                'restaurant_id' => $validated['restaurant_id'],
                'user_id' => auth()->id(),
                'channel' => $validated['channel'],
                'status' => 'pending',
                'payment_status' => 'pending',
                'customer_info' => $validated['customer_info'],
                'delivery_info' => $validated['delivery_info'],
                'items_total' => $itemsTotal,
                'tax_amount' => $taxAmount,
                'delivery_fee' => $deliveryFee,
                'service_fee' => $serviceFee,
                'total_amount' => $totalAmount,
                'currency' => $restaurant->currency,
                'notes' => $validated['notes'] ?? null,
                'special_instructions' => $validated['special_instructions'] ?? null,
                'estimated_prep_time' => $restaurant->settings['estimated_prep_time'] ?? 30,
                'scheduled_at' => $validated['delivery_info']['scheduled_at'] ?? null,
            ]);

            // Generate order number
            $order->update(['order_number' => $order->generateOrderNumber()]);

            // Create order items
            foreach ($orderItems as $item) {
                $order->items()->create($item);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $order->load(['restaurant:id,name,slug,currency', 'items.product:id,name']),
                'message' => 'Order created successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders/{id}",
     *     summary="Get order details",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="order_number", type="string", example="PIZ-000001"),
     *                 @OA\Property(property="status", type="string", example="pending"),
     *                 @OA\Property(property="total_amount", type="string", example="23.98"),
     *                 @OA\Property(property="items", type="array", @OA\Items()),
     *                 @OA\Property(property="restaurant", type="object"),
     *                 @OA\Property(property="user", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     )
     * )
     */
    public function show(Order $order): JsonResponse
    {
        $order->load([
            'restaurant:id,name,slug,currency,phone,address',
            'user:id,name,email,phone',
            'items.product:id,name,images'
        ]);

        return response()->json([
            'success' => true,
            'data' => $order,
            'message' => 'Order retrieved successfully'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/orders/{id}/status",
     *     summary="Update order status",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"pending","confirmed","preparing","ready","out_for_delivery","delivered","cancelled"}, example="confirmed"),
     *             @OA\Property(property="cancellation_reason", type="string", example="Customer request")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order status updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,out_for_delivery,delivered,cancelled',
            'cancellation_reason' => 'required_if:status,cancelled|string|max:500',
        ]);

        $statusTimestamps = [
            'confirmed' => 'confirmed_at',
            'preparing' => 'prepared_at',
            'ready' => 'prepared_at',
            'out_for_delivery' => 'picked_up_at',
            'delivered' => 'delivered_at',
            'cancelled' => 'cancelled_at',
        ];

        $updateData = ['status' => $validated['status']];

        if (isset($statusTimestamps[$validated['status']])) {
            $updateData[$statusTimestamps[$validated['status']]] = now();
        }

        if ($validated['status'] === 'cancelled') {
            $updateData['cancellation_reason'] = $validated['cancellation_reason'];
        }

        $order->update($updateData);

        return response()->json([
            'success' => true,
            'data' => $order->fresh(),
            'message' => 'Order status updated successfully'
        ]);
    }

    /**
     * Cancel order
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        if (!$order->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be cancelled in current status'
            ], 400);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $validated['reason'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $order->fresh(),
            'message' => 'Order cancelled successfully'
        ]);
    }

    /**
     * Get orders by restaurant
     */
    public function getByRestaurant(Restaurant $restaurant, Request $request): JsonResponse
    {
        $query = Order::where('restaurant_id', $restaurant->id)
                     ->with(['user:id,name,email', 'items.product:id,name'])
                     ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        // Get active orders by default
        if (!$request->has('status') && !$request->has('all')) {
            $query->active();
        }

        $orders = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => [
                'restaurant' => $restaurant->only(['id', 'name', 'slug']),
                'orders' => $orders
            ],
            'message' => 'Restaurant orders retrieved successfully'
        ]);
    }
} 