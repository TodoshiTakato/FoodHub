<?php

namespace App\DTOs\Order;

use App\Enums\OrderStatusEnum;
use Illuminate\Http\Request;

class UpdateOrderStatusDTO
{
    public function __construct(
        public readonly OrderStatusEnum $status,
        public readonly ?string $cancellation_reason = null,
    ) {}

    /**
     * Create DTO from Request
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            status: OrderStatusEnum::fromString($request->validated('status')),
            cancellation_reason: $request->validated('cancellation_reason'),
        );
    }

    /**
     * Convert to array for Order update
     */
    public function toArray(): array
    {
        $data = [
            'status' => $this->status->value,
        ];

        if ($this->cancellation_reason) {
            $data['cancellation_reason'] = $this->cancellation_reason;
        }

        // Add timestamp fields based on status
        $timestampFields = [
            OrderStatusEnum::CONFIRMED => 'confirmed_at',
            OrderStatusEnum::PREPARING => 'prepared_at',
            OrderStatusEnum::READY => 'prepared_at',
            OrderStatusEnum::OUT_FOR_DELIVERY => 'picked_up_at',
            OrderStatusEnum::DELIVERED => 'delivered_at',
            OrderStatusEnum::CANCELLED => 'cancelled_at',
        ];

        if (isset($timestampFields[$this->status])) {
            $data[$timestampFields[$this->status]] = now();
        }

        return $data;
    }


} 