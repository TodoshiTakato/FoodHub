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
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            RolePermissionSeeder::class,  // First - create roles and permissions
            RestaurantSeeder::class,      // Second - create restaurants
            CategorySeeder::class,        // Third - create categories for restaurants
            MenuSeeder::class,           // Fourth - create menus for restaurants
            ProductSeeder::class,        // Fifth - create products and attach to menus
        ]);
    }
}
