<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем разрешения
        $permissions = [
            // Рестораны
            'manage-restaurants',
            'view-restaurants',
            
            // Меню и продукты
            'manage-menu',
            'view-menu',
            'manage-products',
            'view-products',
            
            // Заказы
            'manage-orders',
            'view-orders',
            'update-order-status',
            'cancel-orders',
            
            // Пользователи
            'manage-users',
            'view-users',
            
            // Аналитика
            'view-analytics',
            'view-reports',
            
            // Настройки
            'manage-settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Создаем роли и назначаем разрешения
        
        // Super Admin - все разрешения
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Restaurant Owner - управление своим рестораном
        $restaurantOwner = Role::firstOrCreate(['name' => 'restaurant-owner']);
        $restaurantOwner->givePermissionTo([
            'view-restaurants',
            'manage-menu',
            'view-menu', 
            'manage-products',
            'view-products',
            'manage-orders',
            'view-orders',
            'update-order-status',
            'cancel-orders',
            'manage-users',
            'view-users',
            'view-analytics',
            'view-reports',
            'manage-settings',
        ]);

        // Restaurant Manager - управление операциями
        $restaurantManager = Role::firstOrCreate(['name' => 'restaurant-manager']);
        $restaurantManager->givePermissionTo([
            'view-restaurants',
            'manage-menu',
            'view-menu',
            'manage-products', 
            'view-products',
            'manage-orders',
            'view-orders',
            'update-order-status',
            'cancel-orders',
            'view-analytics',
            'view-reports',
        ]);

        // Kitchen Staff - только заказы и меню
        $kitchenStaff = Role::firstOrCreate(['name' => 'kitchen-staff']);
        $kitchenStaff->givePermissionTo([
            'view-menu',
            'view-products',
            'view-orders',
            'update-order-status',
        ]);

        // Cashier - кассир
        $cashier = Role::firstOrCreate(['name' => 'cashier']);
        $cashier->givePermissionTo([
            'view-menu',
            'view-products',
            'manage-orders',
            'view-orders',
            'update-order-status',
        ]);

        // Call Center Operator - оператор колл-центра
        $callCenter = Role::firstOrCreate(['name' => 'call-center-operator']);
        $callCenter->givePermissionTo([
            'view-menu',
            'view-products',
            'manage-orders',
            'view-orders',
            'update-order-status',
            'cancel-orders',
        ]);

        // Courier - курьер
        $courier = Role::firstOrCreate(['name' => 'courier']);
        $courier->givePermissionTo([
            'view-orders',
            'update-order-status',
        ]);

        // Customer - клиент (базовые права)
        $customer = Role::firstOrCreate(['name' => 'customer']);
        $customer->givePermissionTo([
            'view-menu',
            'view-products',
        ]);
    }
}
