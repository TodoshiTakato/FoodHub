<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Enums\StatusEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RestaurantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $restaurants = [
            [
                'name' => 'Pizza Palace',
                'description' => 'Authentic Italian pizza with fresh ingredients',
                'phone' => '+1-555-0101',
                'email' => 'contact@pizzapalace.com',
                'address' => [
                    'en' => '123 Main St, New York, NY 10001',
                    'ru' => 'ул. Главная 123, Нью-Йорк, Нью-Йорк 10001'
                ],
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'business_hours' => [
                    'monday' => ['open' => '10:00', 'close' => '22:00'],
                    'tuesday' => ['open' => '10:00', 'close' => '22:00'],
                    'wednesday' => ['open' => '10:00', 'close' => '22:00'],
                    'thursday' => ['open' => '10:00', 'close' => '22:00'],
                    'friday' => ['open' => '10:00', 'close' => '23:00'],
                    'saturday' => ['open' => '11:00', 'close' => '23:00'],
                    'sunday' => ['open' => '11:00', 'close' => '21:00']
                ],
                'languages' => ['en', 'ru'],
                'default_language' => 'en',
                'currency' => 'USD',
                'timezone' => 'America/New_York'
            ],
            [
                'name' => 'Burger Express',
                'description' => 'Fast and delicious burgers for busy people',
                'phone' => '+1-555-0102',
                'email' => 'info@burgerexpress.com',
                'address' => [
                    'en' => '456 Broadway Ave, Los Angeles, CA 90210',
                    'ru' => 'Бродвей авеню 456, Лос-Анджелес, Калифорния 90210'
                ],
                'latitude' => 34.0522,
                'longitude' => -118.2437,
                'business_hours' => [
                    'monday' => ['open' => '09:00', 'close' => '21:00'],
                    'tuesday' => ['open' => '09:00', 'close' => '21:00'],
                    'wednesday' => ['open' => '09:00', 'close' => '21:00'],
                    'thursday' => ['open' => '09:00', 'close' => '21:00'],
                    'friday' => ['open' => '09:00', 'close' => '22:00'],
                    'saturday' => ['open' => '10:00', 'close' => '22:00'],
                    'sunday' => ['open' => '10:00', 'close' => '20:00']
                ],
                'languages' => ['en', 'es'],
                'default_language' => 'en',
                'currency' => 'USD',
                'timezone' => 'America/Los_Angeles'
            ],
            [
                'name' => 'Sushi Zen',
                'description' => 'Traditional Japanese sushi and modern fusion',
                'phone' => '+1-555-0103',
                'email' => 'hello@sushizen.com',
                'address' => [
                    'en' => '789 Ocean Dr, Miami, FL 33139',
                    'ru' => 'Океан драйв 789, Майами, Флорида 33139'
                ],
                'latitude' => 25.7617,
                'longitude' => -80.1918,
                'business_hours' => [
                    'monday' => ['closed' => true],
                    'tuesday' => ['open' => '17:00', 'close' => '23:00'],
                    'wednesday' => ['open' => '17:00', 'close' => '23:00'],
                    'thursday' => ['open' => '17:00', 'close' => '23:00'],
                    'friday' => ['open' => '17:00', 'close' => '24:00'],
                    'saturday' => ['open' => '16:00', 'close' => '24:00'],
                    'sunday' => ['open' => '16:00', 'close' => '22:00']
                ],
                'languages' => ['en', 'ja'],
                'default_language' => 'en',
                'currency' => 'USD',
                'timezone' => 'America/New_York'
            ]
        ];

        foreach ($restaurants as $restaurantData) {
            $restaurantData['slug'] = Str::slug($restaurantData['name']);
            $restaurantData['status'] = StatusEnum::ACTIVE->value;
            $restaurantData['verified_at'] = now();
            
            Restaurant::create($restaurantData);
        }
    }
}
