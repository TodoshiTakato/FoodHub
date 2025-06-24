<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Order $order,
        public string $oldStatus,
        public string $newStatus
    ) {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('restaurant.' . $this->order->restaurant_id),
            new PrivateChannel('orders'),
            new PrivateChannel('order.' . $this->order->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'order.status.changed';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'order' => [
                'id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'status' => $this->order->status->toString(),           // API строка
                'old_status' => $this->oldStatus,                       // Уже строка
                'new_status' => $this->newStatus,                       // Уже строка
                'total_amount' => $this->order->total_amount,
                'currency' => $this->order->currency,
                'channel' => $this->order->channel->toString(),         // API строка
                'customer_info' => $this->order->customer_info,
                'restaurant_id' => $this->order->restaurant_id,
                'updated_at' => $this->order->updated_at,
            ]
        ];
    }
}
