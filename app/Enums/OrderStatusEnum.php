<?php

namespace App\Enums;

enum OrderStatusEnum: int
{
    case PENDING = 0;
    case CONFIRMED = 1;
    case PREPARING = 2;
    case READY = 3;
    case OUT_FOR_DELIVERY = 4;
    case DELIVERED = 5;
    case CANCELLED = 6;

    /**
     * Get status label for display
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::PREPARING => 'Preparing',
            self::READY => 'Ready',
            self::OUT_FOR_DELIVERY => 'Out for Delivery',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
        };
    }

    /**
     * Get status color for UI
     */
    public function color(): string
    {
        return match($this) {
            self::PENDING => '#FFA500',      // Orange
            self::CONFIRMED => '#4169E1',    // Royal Blue
            self::PREPARING => '#FF6347',    // Tomato
            self::READY => '#32CD32',        // Lime Green
            self::OUT_FOR_DELIVERY => '#9370DB', // Medium Purple
            self::DELIVERED => '#228B22',    // Forest Green
            self::CANCELLED => '#DC143C',    // Crimson
        };
    }

    /**
     * Check if order is active (not final state)
     */
    public function isActive(): bool
    {
        return match($this) {
            self::PENDING,
            self::CONFIRMED,
            self::PREPARING,
            self::READY,
            self::OUT_FOR_DELIVERY => true,
            self::DELIVERED,
            self::CANCELLED => false,
        };
    }

    /**
     * Check if order is completed
     */
    public function isCompleted(): bool
    {
        return $this === self::DELIVERED;
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return match($this) {
            self::PENDING,
            self::CONFIRMED => true,
            default => false,
        };
    }

    /**
     * Get all active statuses
     */
    public static function activeStatuses(): array
    {
        return [
            self::PENDING,
            self::CONFIRMED,
            self::PREPARING,
            self::READY,
            self::OUT_FOR_DELIVERY,
        ];
    }

    /**
     * Convert from string to enum (for backward compatibility)
     */
    public static function fromString(string $status): ?self
    {
        return match($status) {
            'pending' => self::PENDING,
            'confirmed' => self::CONFIRMED,
            'preparing' => self::PREPARING,
            'ready' => self::READY,
            'out_for_delivery' => self::OUT_FOR_DELIVERY,
            'delivered' => self::DELIVERED,
            'cancelled' => self::CANCELLED,
            default => null,
        };
    }

    /**
     * Get all enum values as array for validation (используем встроенный value)
     */
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    /**
     * Get all string names for validation (используем встроенный name)
     */
    public static function names(): array
    {
        return array_map(fn($case) => strtolower($case->name), self::cases());
    }

    /**
     * Convert enum to API string format
     */
    public function toString(): string
    {
        return match($this) {
            self::PENDING => 'pending',
            self::CONFIRMED => 'confirmed',
            self::PREPARING => 'preparing',
            self::READY => 'ready',
            self::OUT_FOR_DELIVERY => 'out_for_delivery',
            self::DELIVERED => 'delivered',
            self::CANCELLED => 'cancelled',
        };
    }

    /**
     * Get all string values for API (используем fromString logic)
     */
    public static function strings(): array
    {
        return [
            'pending',
            'confirmed',
            'preparing',
            'ready',
            'out_for_delivery',
            'delivered',
            'cancelled',
        ];
    }


} 