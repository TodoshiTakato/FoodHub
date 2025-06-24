<?php

namespace App\DTOs\Order;

use App\Enums\OrderChannelEnum;
use Illuminate\Http\Request;

class CreateOrderDTO
{
    public function __construct(
        public readonly int $restaurant_id,
        public readonly OrderChannelEnum $channel,
        public readonly array $customer_info,
        public readonly array $delivery_info,
        public readonly array $items,
        public readonly ?string $notes = null,
        public readonly ?string $special_instructions = null,
        public readonly ?string $scheduled_at = null,
    ) {}

    /**
     * Create DTO from Request
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            restaurant_id: $request->validated('restaurant_id'),
            channel: OrderChannelEnum::fromString($request->validated('channel')),
            customer_info: $request->validated('customer_info'),
            delivery_info: $request->validated('delivery_info'),
            items: $request->validated('items'),
            notes: $request->validated('notes'),
            special_instructions: $request->validated('special_instructions'),
            scheduled_at: $request->validated('delivery_info.scheduled_at'),
        );
    }

    /**
     * Convert to array for Order creation
     */
    public function toArray(): array
    {
        return [
            'restaurant_id' => $this->restaurant_id,
            'channel' => $this->channel->value,
            'customer_info' => $this->customer_info,
            'delivery_info' => $this->delivery_info,
            'notes' => $this->notes,
            'special_instructions' => $this->special_instructions,
            'scheduled_at' => $this->scheduled_at,
        ];
    }


} 