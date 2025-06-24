<?php

namespace App\DTOs\Product;

use App\Enums\ProductTypeEnum;
use Illuminate\Http\Request;

class CreateProductDTO
{
    public function __construct(
        public readonly int $restaurant_id,
        public readonly int $category_id,
        public readonly array $name,
        public readonly ProductTypeEnum $type,
        public readonly array $prices,
        public readonly array $channels,
        public readonly ?array $description = null,
        public readonly ?string $sku = null,
        public readonly ?array $images = null,
        public readonly ?float $cost_price = null,
        public readonly ?int $calories = null,
        public readonly ?array $allergens = null,
        public readonly ?array $ingredients = null,
        public readonly ?array $nutritional_info = null,
        public readonly ?array $modifiers = null,
        public readonly ?array $combo_items = null,
        public readonly ?array $availability_hours = null,
        public readonly ?int $sort_order = null,
        public readonly ?int $stock_quantity = null,
        public readonly ?bool $track_stock = null,
        public readonly ?array $settings = null,
    ) {}

    /**
     * Create DTO from Request
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            restaurant_id: $request->validated('restaurant_id'),
            category_id: $request->validated('category_id'),
            name: $request->validated('name'),
            type: ProductTypeEnum::fromString($request->validated('type')),
            prices: $request->validated('prices'),
            channels: $request->validated('channels'),
            description: $request->validated('description'),
            sku: $request->validated('sku'),
            images: $request->validated('images'),
            cost_price: $request->validated('cost_price'),
            calories: $request->validated('calories'),
            allergens: $request->validated('allergens'),
            ingredients: $request->validated('ingredients'),
            nutritional_info: $request->validated('nutritional_info'),
            modifiers: $request->validated('modifiers'),
            combo_items: $request->validated('combo_items'),
            availability_hours: $request->validated('availability_hours'),
            sort_order: $request->validated('sort_order'),
            stock_quantity: $request->validated('stock_quantity'),
            track_stock: $request->validated('track_stock'),
            settings: $request->validated('settings'),
        );
    }

    /**
     * Convert to array for Product creation
     */
    public function toArray(): array
    {
        return [
            'restaurant_id' => $this->restaurant_id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'type' => $this->type->value,  // Используем ->value для БД
            'prices' => $this->prices,
            'channels' => $this->channels,
            'description' => $this->description,
            'sku' => $this->sku,
            'images' => $this->images,
            'cost_price' => $this->cost_price,
            'calories' => $this->calories,
            'allergens' => $this->allergens,
            'ingredients' => $this->ingredients,
            'nutritional_info' => $this->nutritional_info,
            'modifiers' => $this->modifiers,
            'combo_items' => $this->combo_items,
            'availability_hours' => $this->availability_hours,
            'sort_order' => $this->sort_order,
            'stock_quantity' => $this->stock_quantity,
            'track_stock' => $this->track_stock,
            'settings' => $this->settings,
            'is_active' => true,
            'is_featured' => false,
        ];
    }
} 