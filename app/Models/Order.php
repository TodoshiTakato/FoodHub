<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Traits\UsesUtcTimestamps;

class Order extends Model
{
    use HasFactory, UsesUtcTimestamps;

    protected $fillable = [
        'restaurant_id',
        'user_id',
        'order_number',
        'channel',
        'status',
        'payment_status',
        'payment_method',
        'customer_info',
        'delivery_info',
        'items_total',
        'tax_amount',
        'delivery_fee',
        'service_fee',
        'discount_amount',
        'total_amount',
        'currency',
        'notes',
        'special_instructions',
        'estimated_prep_time',
        'estimated_delivery_time',
        'scheduled_at',
        'confirmed_at',
        'prepared_at',
        'picked_up_at',
        'delivered_at',
        'cancelled_at',
        'cancellation_reason',
        'metadata',
    ];

    protected $casts = [
        'customer_info' => 'array',
        'delivery_info' => 'array',
        'items_total' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'estimated_prep_time' => 'integer',
        'estimated_delivery_time' => 'integer',
        'scheduled_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'prepared_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relationships
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery']);
    }

    // Helper methods
    public function generateOrderNumber()
    {
        $prefix = strtoupper(substr($this->restaurant->name, 0, 3));
        $number = str_pad($this->id, 6, '0', STR_PAD_LEFT);
        return $prefix . '-' . $number;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function isDelivery(): bool
    {
        return isset($this->delivery_info['type']) && $this->delivery_info['type'] === 'delivery';
    }

    public function isPickup(): bool
    {
        return isset($this->delivery_info['type']) && $this->delivery_info['type'] === 'pickup';
    }
}
