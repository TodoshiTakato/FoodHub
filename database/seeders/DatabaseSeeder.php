<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,  // First - create roles and permissions
            RestaurantSeeder::class,      // Second - create restaurants
            UserSeeder::class,           // Third - create test users with roles
            CategorySeeder::class,        // Fourth - create categories for restaurants
            MenuSeeder::class,           // Fifth - create menus for restaurants
            ProductSeeder::class,        // Sixth - create products and attach to menus
        ]);
    }
}
