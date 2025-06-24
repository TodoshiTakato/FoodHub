<?php

namespace App\DTOs\Menu;

use App\Enums\MenuTypeEnum;
use Illuminate\Http\Request;

class CreateMenuDTO
{
    public function __construct(
        public readonly int $restaurant_id,
        public readonly array $name,
        public readonly MenuTypeEnum $type,
        public readonly array $channels,
        public readonly ?array $description = null,
        public readonly ?array $availability_hours = null,
        public readonly ?int $sort_order = null,
        public readonly ?array $settings = null,
    ) {}

    /**
     * Create DTO from Request
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            restaurant_id: $request->validated('restaurant_id'),
            name: $request->validated('name'),
            type: MenuTypeEnum::fromString($request->validated('type')),
            channels: $request->validated('channels'),
            description: $request->validated('description'),
            availability_hours: $request->validated('availability_hours'),
            sort_order: $request->validated('sort_order'),
            settings: $request->validated('settings'),
        );
    }

    /**
     * Convert to array for Menu creation
     */
    public function toArray(): array
    {
        return [
            'restaurant_id' => $this->restaurant_id,
            'name' => $this->name,
            'type' => $this->type->value,  // Используем ->value для БД
            'channels' => $this->channels,
            'description' => $this->description,
            'availability_hours' => $this->availability_hours,
            'sort_order' => $this->sort_order,
            'settings' => $this->settings,
            'is_active' => true,
        ];
    }
} 