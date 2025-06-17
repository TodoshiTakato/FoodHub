<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Restaurant;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $restaurants = Restaurant::all();

        foreach ($restaurants as $restaurant) {
            // Main Menu
            $mainMenu = Menu::create([
                'restaurant_id' => $restaurant->id,
                'name' => [
                    'en' => 'Main Menu',
                    'ru' => 'Основное меню'
                ],
                'slug' => 'main-menu-' . $restaurant->slug,
                'description' => [
                    'en' => 'Our complete menu with all available dishes',
                    'ru' => 'Наше полное меню со всеми доступными блюдами'
                ],
                'type' => 'main',
                'channels' => ['web', 'mobile', 'pos'],
                'availability_hours' => [
                    'monday' => ['09:00', '22:00'],
                    'tuesday' => ['09:00', '22:00'],
                    'wednesday' => ['09:00', '22:00'],
                    'thursday' => ['09:00', '22:00'],
                    'friday' => ['09:00', '23:00'],
                    'saturday' => ['09:00', '23:00'],
                    'sunday' => ['10:00', '21:00']
                ],
                'is_active' => true,
                'sort_order' => 1,
                'settings' => [
                    'show_prices' => true,
                    'show_descriptions' => true,
                    'show_images' => true
                ]
            ]);

            // Breakfast Menu (only for pizza place and burger express)
            if (in_array($restaurant->slug, ['pizza-palace', 'burger-express'])) {
                Menu::create([
                    'restaurant_id' => $restaurant->id,
                    'name' => [
                        'en' => 'Breakfast Menu',
                        'ru' => 'Завтрак'
                    ],
                    'slug' => 'breakfast-menu-' . $restaurant->slug,
                    'description' => [
                        'en' => 'Start your day with our delicious breakfast options',
                        'ru' => 'Начните день с наших вкусных завтраков'
                    ],
                    'type' => 'breakfast',
                    'channels' => ['web', 'mobile'],
                    'availability_hours' => [
                        'monday' => ['07:00', '11:00'],
                        'tuesday' => ['07:00', '11:00'],
                        'wednesday' => ['07:00', '11:00'],
                        'thursday' => ['07:00', '11:00'],
                        'friday' => ['07:00', '11:00'],
                        'saturday' => ['08:00', '12:00'],
                        'sunday' => ['08:00', '12:00']
                    ],
                    'is_active' => true,
                    'sort_order' => 2
                ]);
            }

            // Lunch Menu
            Menu::create([
                'restaurant_id' => $restaurant->id,
                'name' => [
                    'en' => 'Lunch Specials',
                    'ru' => 'Обеденные спецпредложения'
                ],
                'slug' => 'lunch-specials-' . $restaurant->slug,
                'description' => [
                    'en' => 'Special lunch offers at great prices',
                    'ru' => 'Специальные обеденные предложения по отличным ценам'
                ],
                'type' => 'lunch',
                'channels' => ['web', 'mobile', 'phone', 'pos'],
                'availability_hours' => [
                    'monday' => ['11:00', '16:00'],
                    'tuesday' => ['11:00', '16:00'],
                    'wednesday' => ['11:00', '16:00'],
                    'thursday' => ['11:00', '16:00'],
                    'friday' => ['11:00', '16:00'],
                    'saturday' => ['12:00', '16:00'],
                    'sunday' => ['12:00', '16:00']
                ],
                'is_active' => true,
                'sort_order' => 3
            ]);

            // Drinks Menu
            Menu::create([
                'restaurant_id' => $restaurant->id,
                'name' => [
                    'en' => 'Beverages',
                    'ru' => 'Напитки'
                ],
                'slug' => 'beverages-' . $restaurant->slug,
                'description' => [
                    'en' => 'Refreshing drinks and beverages',
                    'ru' => 'Освежающие напитки'
                ],
                'type' => 'drinks',
                'channels' => ['web', 'mobile', 'telegram', 'whatsapp', 'phone', 'pos'],
                'availability_hours' => [
                    'monday' => ['09:00', '22:00'],
                    'tuesday' => ['09:00', '22:00'],
                    'wednesday' => ['09:00', '22:00'],
                    'thursday' => ['09:00', '22:00'],
                    'friday' => ['09:00', '23:00'],
                    'saturday' => ['09:00', '23:00'],
                    'sunday' => ['10:00', '21:00']
                ],
                'is_active' => true,
                'sort_order' => 4
            ]);
        }

        $this->command->info('Menus created successfully!');
    }
}
