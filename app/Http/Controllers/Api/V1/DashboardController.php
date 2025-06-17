<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function stats(Request $request): JsonResponse
    {
        // For now, return mock data
        $stats = [
            'total_orders' => rand(120, 180),
            'pending_orders' => rand(5, 25),
            'total_revenue' => rand(10000, 15000) + (rand(0, 99) / 100),
            'today_revenue' => rand(800, 1200) + (rand(0, 99) / 100),
            'recent_orders' => $this->generateMockOrders(5)
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Dashboard statistics retrieved successfully'
        ]);
    }

    /**
     * Get revenue analytics
     */
    public function revenue(Request $request): JsonResponse
    {
        $revenue = [
            'total_revenue' => rand(50000, 80000) + (rand(0, 99) / 100),
            'previous_period_revenue' => rand(40000, 70000) + (rand(0, 99) / 100),
            'growth_percentage' => rand(5, 25) + (rand(0, 99) / 100),
            'revenue_by_period' => $this->generateRevenueData()
        ];

        return response()->json([
            'success' => true,
            'data' => $revenue,
            'message' => 'Revenue analytics retrieved successfully'
        ]);
    }

    /**
     * Get orders analytics
     */
    public function orders(Request $request): JsonResponse
    {
        $orders = [
            'total_orders' => rand(500, 800),
            'pending_orders' => rand(10, 30),
            'completed_orders' => rand(450, 750),
            'cancelled_orders' => rand(5, 20),
            'orders_by_hour' => $this->generateOrdersByHour(),
            'orders_by_channel' => [
                ['channel' => 'delivery', 'count' => rand(200, 400), 'percentage' => rand(40, 60)],
                ['channel' => 'pickup', 'count' => rand(100, 200), 'percentage' => rand(20, 35)],
                ['channel' => 'dine_in', 'count' => rand(50, 150), 'percentage' => rand(15, 25)]
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $orders,
            'message' => 'Orders analytics retrieved successfully'
        ]);
    }

    /**
     * Get top products
     */
    public function topProducts(Request $request): JsonResponse
    {
        $products = [
            [
                'product_id' => 1,
                'product_name' => 'Margherita Pizza',
                'orders_count' => rand(45, 85),
                'revenue' => rand(800, 1500) + (rand(0, 99) / 100),
                'trend' => collect(['up', 'down', 'stable'])->random()
            ],
            [
                'product_id' => 2,
                'product_name' => 'Chicken Burger',
                'orders_count' => rand(35, 65),
                'revenue' => rand(600, 1200) + (rand(0, 99) / 100),
                'trend' => collect(['up', 'down', 'stable'])->random()
            ],
            [
                'product_id' => 3,
                'product_name' => 'Caesar Salad',
                'orders_count' => rand(25, 45),
                'revenue' => rand(400, 800) + (rand(0, 99) / 100),
                'trend' => collect(['up', 'down', 'stable'])->random()
            ],
            [
                'product_id' => 4,
                'product_name' => 'Pasta Carbonara',
                'orders_count' => rand(30, 50),
                'revenue' => rand(500, 900) + (rand(0, 99) / 100),
                'trend' => collect(['up', 'down', 'stable'])->random()
            ],
            [
                'product_id' => 5,
                'product_name' => 'Fish & Chips',
                'orders_count' => rand(20, 40),
                'revenue' => rand(350, 700) + (rand(0, 99) / 100),
                'trend' => collect(['up', 'down', 'stable'])->random()
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $products,
            'message' => 'Top products retrieved successfully'
        ]);
    }

    /**
     * Generate mock order data
     */
    private function generateMockOrders(int $count): array
    {
        $orders = [];
        $customers = ['John Doe', 'Jane Smith', 'Mike Johnson', 'Sarah Wilson', 'Tom Brown', 'Lisa Davis'];
        $statuses = ['pending', 'preparing', 'ready', 'completed'];

        for ($i = 0; $i < $count; $i++) {
            $orders[] = [
                'id' => $i + 1,
                'order_number' => 'ORD' . str_pad($i + 1000, 4, '0', STR_PAD_LEFT),
                'customer_name' => $customers[array_rand($customers)],
                'total' => rand(15, 75) + (rand(0, 99) / 100),
                'status' => $statuses[array_rand($statuses)],
                'created_at' => now()->subMinutes(rand(5, 120))->toISOString()
            ];
        }

        return $orders;
    }

    /**
     * Generate revenue data by period
     */
    private function generateRevenueData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $data[] = [
                'period' => now()->subDays($i)->format('Y-m-d'),
                'revenue' => rand(800, 2000) + (rand(0, 99) / 100),
                'orders_count' => rand(20, 50)
            ];
        }
        return $data;
    }

    /**
     * Generate orders by hour data
     */
    private function generateOrdersByHour(): array
    {
        $data = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $count = $hour >= 11 && $hour <= 14 ? rand(15, 35) : // Lunch rush
                     ($hour >= 18 && $hour <= 21 ? rand(20, 40) : // Dinner rush
                     rand(0, 10)); // Other hours
            
            $data[] = [
                'hour' => $hour,
                'count' => $count
            ];
        }
        return $data;
    }
} 