<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\Menu;
use App\Enums\ProductTypeEnum;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $restaurants = Restaurant::all();

        foreach ($restaurants as $restaurant) {
            $categories = Category::where('restaurant_id', $restaurant->id)->get();
            
            foreach ($categories as $category) {
                $this->createProductsForCategory($restaurant, $category);
            }
            
            // Attach products to menus
            $this->attachProductsToMenus($restaurant);
        }

        $this->command->info('Products created successfully!');
    }

    private function createProductsForCategory($restaurant, $category)
    {
        $categorySlug = $category->slug;
        
        switch ($categorySlug) {
            case 'pizza':
                $products = [
                    [
                        'name' => ['en' => 'Margherita Pizza', 'ru' => 'Пицца Маргарита'],
                        'description' => ['en' => 'Classic pizza with tomato sauce, mozzarella and basil', 'ru' => 'Классическая пицца с томатным соусом, моцареллой и базиликом'],
                        'prices' => ['web' => 12.99, 'mobile' => 12.99, 'pos' => 12.99],
                        'images' => ['https://example.com/margherita.jpg'],
                        'calories' => 250,
                        'is_featured' => true
                    ],
                    [
                        'name' => ['en' => 'Pepperoni Pizza', 'ru' => 'Пицца Пепперони'],
                        'description' => ['en' => 'Pizza with pepperoni, mozzarella and tomato sauce', 'ru' => 'Пицца с пепперони, моцареллой и томатным соусом'],
                        'prices' => ['web' => 15.99, 'mobile' => 15.99, 'pos' => 15.99],
                        'images' => ['https://example.com/pepperoni.jpg'],
                        'calories' => 320,
                        'is_featured' => true
                    ],
                    [
                        'name' => ['en' => 'Four Cheese Pizza', 'ru' => 'Пицца Четыре сыра'],
                        'description' => ['en' => 'Pizza with four types of cheese', 'ru' => 'Пицца с четырьмя видами сыра'],
                        'prices' => ['web' => 17.99, 'mobile' => 17.99, 'pos' => 17.99],
                        'images' => ['https://example.com/four-cheese.jpg'],
                        'calories' => 380
                    ]
                ];
                break;

            case 'burgers':
                $products = [
                    [
                        'name' => ['en' => 'Classic Burger', 'ru' => 'Классический бургер', 'uz' => 'Klassik burger'],
                        'description' => ['en' => 'Beef patty with lettuce, tomato, onion and sauce', 'ru' => 'Говяжья котлета с салатом, помидором, луком и соусом', 'uz' => 'Mol go\'shti kotletasi, salat, pomidor, piyoz va sous bilan'],
                        'prices' => ['web' => 9.99, 'mobile' => 9.99, 'pos' => 9.99],
                        'images' => ['https://example.com/classic-burger.jpg'],
                        'calories' => 450,
                        'is_featured' => true
                    ],
                    [
                        'name' => ['en' => 'Cheeseburger', 'ru' => 'Чизбургер', 'uz' => 'Pishloqli burger'],
                        'description' => ['en' => 'Classic burger with cheese', 'ru' => 'Классический бургер с сыром', 'uz' => 'Pishloq bilan klassik burger'],
                        'prices' => ['web' => 11.99, 'mobile' => 11.99, 'pos' => 11.99],
                        'images' => ['https://example.com/cheeseburger.jpg'],
                        'calories' => 520,
                        'is_featured' => true
                    ],
                    [
                        'name' => ['en' => 'Double Burger', 'ru' => 'Двойной бургер'],
                        'description' => ['en' => 'Double beef patty burger', 'ru' => 'Бургер с двойной говяжьей котлетой'],
                        'prices' => ['web' => 14.99, 'mobile' => 14.99, 'pos' => 14.99],
                        'images' => ['https://example.com/double-burger.jpg'],
                        'calories' => 680
                    ]
                ];
                break;

            case 'sushi':
                $products = [
                    [
                        'name' => ['en' => 'Salmon Roll', 'ru' => 'Ролл с лососем'],
                        'description' => ['en' => 'Fresh salmon roll with rice and nori', 'ru' => 'Свежий ролл с лососем, рисом и нори'],
                        'prices' => ['web' => 8.99, 'mobile' => 8.99, 'pos' => 8.99],
                        'images' => ['https://example.com/salmon-roll.jpg'],
                        'calories' => 180,
                        'is_featured' => true
                    ],
                    [
                        'name' => ['en' => 'California Roll', 'ru' => 'Калифорния ролл'],
                        'description' => ['en' => 'Crab, avocado and cucumber roll', 'ru' => 'Ролл с крабом, авокадо и огурцом'],
                        'prices' => ['web' => 7.99, 'mobile' => 7.99, 'pos' => 7.99],
                        'images' => ['https://example.com/california-roll.jpg'],
                        'calories' => 160,
                        'is_featured' => true
                    ],
                    [
                        'name' => ['en' => 'Dragon Roll', 'ru' => 'Дракон ролл'],
                        'description' => ['en' => 'Eel and avocado roll with special sauce', 'ru' => 'Ролл с угрем и авокадо со специальным соусом'],
                        'prices' => ['web' => 12.99, 'mobile' => 12.99, 'pos' => 12.99],
                        'images' => ['https://example.com/dragon-roll.jpg'],
                        'calories' => 220
                    ]
                ];
                break;

            case 'drinks':
                $products = [
                    [
                        'name' => ['en' => 'Coca-Cola', 'ru' => 'Кока-Кола'],
                        'description' => ['en' => 'Classic Coca-Cola soft drink', 'ru' => 'Классический безалкогольный напиток Кока-Кола'],
                        'prices' => ['web' => 2.99, 'mobile' => 2.99, 'pos' => 2.99, 'telegram' => 2.99, 'whatsapp' => 2.99],
                        'images' => ['https://example.com/coca-cola.jpg'],
                        'calories' => 140
                    ],
                    [
                        'name' => ['en' => 'Orange Juice', 'ru' => 'Апельсиновый сок'],
                        'description' => ['en' => 'Fresh orange juice', 'ru' => 'Свежий апельсиновый сок'],
                        'prices' => ['web' => 3.99, 'mobile' => 3.99, 'pos' => 3.99, 'telegram' => 3.99, 'whatsapp' => 3.99],
                        'images' => ['https://example.com/orange-juice.jpg'],
                        'calories' => 110
                    ],
                    [
                        'name' => ['en' => 'Coffee', 'ru' => 'Кофе'],
                        'description' => ['en' => 'Freshly brewed coffee', 'ru' => 'Свежесваренный кофе'],
                        'prices' => ['web' => 2.49, 'mobile' => 2.49, 'pos' => 2.49, 'telegram' => 2.49, 'whatsapp' => 2.49],
                        'images' => ['https://example.com/coffee.jpg'],
                        'calories' => 5
                    ]
                ];
                break;

            case 'desserts':
                $products = [
                    [
                        'name' => ['en' => 'Chocolate Cake', 'ru' => 'Шоколадный торт'],
                        'description' => ['en' => 'Rich chocolate cake with cream', 'ru' => 'Насыщенный шоколадный торт со сливками'],
                        'prices' => ['web' => 5.99, 'mobile' => 5.99, 'pos' => 5.99],
                        'images' => ['https://example.com/chocolate-cake.jpg'],
                        'calories' => 350,
                        'is_featured' => true
                    ],
                    [
                        'name' => ['en' => 'Ice Cream', 'ru' => 'Мороженое'],
                        'description' => ['en' => 'Vanilla ice cream with toppings', 'ru' => 'Ванильное мороженое с топпингами'],
                        'prices' => ['web' => 3.99, 'mobile' => 3.99, 'pos' => 3.99],
                        'images' => ['https://example.com/ice-cream.jpg'],
                        'calories' => 200
                    ]
                ];
                break;

            default:
                $products = [];
        }

        foreach ($products as $productData) {
            Product::create([
                'restaurant_id' => $restaurant->id,
                'category_id' => $category->id,
                'name' => $productData['name'],
                'slug' => \Str::slug($productData['name']['en']) . '-' . $restaurant->id,
                'description' => $productData['description'],
                'sku' => strtoupper(substr($category->slug, 0, 3)) . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'type' => ProductTypeEnum::SIMPLE->value,
                'images' => $productData['images'] ?? [],
                'prices' => $productData['prices'],
                'cost_price' => ($productData['prices']['web'] ?? 0) * 0.6, // 60% of selling price
                'calories' => $productData['calories'] ?? null,
                'allergens' => [],
                'ingredients' => [],
                'nutritional_info' => [],
                'modifiers' => [],
                'combo_items' => [],
                'channels' => array_keys($productData['prices']),
                'availability_hours' => [],
                'is_active' => true,
                'is_featured' => $productData['is_featured'] ?? false,
                'sort_order' => 0,
                'stock_quantity' => null,
                'track_stock' => false,
                'settings' => []
            ]);
        }
    }

    private function attachProductsToMenus($restaurant)
    {
        $menus = Menu::where('restaurant_id', $restaurant->id)->get();
        $products = Product::where('restaurant_id', $restaurant->id)->get();

        foreach ($menus as $menu) {
            $menuProducts = [];
            
            switch ($menu->type) {
                case 'main':
                    // Attach all products to main menu
                    $menuProducts = $products->pluck('id')->toArray();
                    break;
                    
                case 'breakfast':
                    // Only drinks and some light items for breakfast
                    $menuProducts = $products->whereIn('category.slug', ['drinks'])->pluck('id')->toArray();
                    break;
                    
                case 'lunch':
                    // Main dishes for lunch
                    $menuProducts = $products->whereNotIn('category.slug', ['desserts'])->pluck('id')->toArray();
                    break;
                    
                case 'drinks':
                    // Only drinks
                    $menuProducts = $products->where('category.slug', 'drinks')->pluck('id')->toArray();
                    break;
            }

            // Attach products with sort order and featured status
            $attachData = [];
            foreach ($menuProducts as $index => $productId) {
                $product = $products->find($productId);
                $attachData[$productId] = [
                    'sort_order' => $index + 1,
                    'is_featured' => $product->is_featured ?? false,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            
            $menu->products()->attach($attachData);
        }
    }
}
