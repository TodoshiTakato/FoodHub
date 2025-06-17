<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $restaurants = Restaurant::all();

        foreach ($restaurants as $restaurant) {
            $categories = [
                [
                    'name' => ['en' => 'Pizza', 'ru' => 'Пицца'],
                    'description' => ['en' => 'Fresh baked pizza', 'ru' => 'Свежая выпечка пиццы'],
                ],
                [
                    'name' => ['en' => 'Burgers', 'ru' => 'Бургеры'],
                    'description' => ['en' => 'Juicy burgers', 'ru' => 'Сочные бургеры'],
                ],
                [
                    'name' => ['en' => 'Drinks', 'ru' => 'Напитки'],
                    'description' => ['en' => 'Cold and hot drinks', 'ru' => 'Холодные и горячие напитки'],
                ],
                [
                    'name' => ['en' => 'Desserts', 'ru' => 'Десерты'],
                    'description' => ['en' => 'Sweet desserts', 'ru' => 'Сладкие десерты'],
                ],
            ];

            foreach ($categories as $index => $categoryData) {
                Category::create([
                    'restaurant_id' => $restaurant->id,
                    'name' => $categoryData['name'],
                    'slug' => Str::slug($categoryData['name']['en']),
                    'description' => $categoryData['description'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);
            }
        }
    }
}
