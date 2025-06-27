<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\StatusEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Очищаем существующих пользователей
        User::truncate();

        // Создаем тестовых пользователей для разработки
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@foodhub.com',
                'password' => Hash::make('admin123'),
                'phone' => '+998901234567',
                'restaurant_id' => null,
                'status' => StatusEnum::ACTIVE->value,
                'role' => 'super-admin'
            ],
            [
                'name' => 'System Admin',
                'email' => 'sysadmin@foodhub.com',
                'password' => Hash::make('sysadmin123'),
                'phone' => '+998901234577',
                'restaurant_id' => null,
                'status' => StatusEnum::ACTIVE->value,
                'role' => 'admin'
            ],
            [
                'name' => 'Restaurant Owner',
                'email' => 'owner@pizzapalace.com',
                'password' => Hash::make('owner123'),
                'phone' => '+998901234568',
                'restaurant_id' => 1, // Pizza Palace
                'status' => StatusEnum::ACTIVE->value,
                'role' => 'restaurant-owner'
            ],
            [
                'name' => 'Restaurant Manager',
                'email' => 'manager@foodhub.com',
                'password' => Hash::make('manager123'),
                'phone' => '+998901234569',
                'restaurant_id' => 1,
                'status' => StatusEnum::ACTIVE->value,
                'role' => 'restaurant-manager'
            ],
            [
                'name' => 'Kitchen Staff',
                'email' => 'staff@foodhub.com',
                'password' => Hash::make('staff123'),
                'phone' => '+998901234570',
                'restaurant_id' => 1,
                'status' => StatusEnum::ACTIVE->value,
                'role' => 'kitchen-staff'
            ],
            [
                'name' => 'Cashier',
                'email' => 'cashier@foodhub.com',
                'password' => Hash::make('cashier123'),
                'phone' => '+998901234574',
                'restaurant_id' => 1,
                'status' => StatusEnum::ACTIVE->value,
                'role' => 'cashier'
            ],
            [
                'name' => 'Test User 1',
                'email' => 'testuser1@foodhub.com',
                'password' => Hash::make('test123'),
                'phone' => '+998901234571',
                'restaurant_id' => null,
                'status' => StatusEnum::ACTIVE->value,
                'role' => 'customer'
            ],
            [
                'name' => 'Test User 2',
                'email' => 'testuser2@foodhub.com',
                'password' => Hash::make('test123'),
                'phone' => '+998901234572',
                'restaurant_id' => null,
                'status' => StatusEnum::ACTIVE->value,
                'role' => 'customer'
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::create($userData);
            
            // Назначаем роль пользователю
            $user->assignRole($role);

            $this->command->info("Created user: {$user->name} ({$user->email}) with role: {$role}");
        }

        $this->command->info('Users seeded successfully!');
    }
} 