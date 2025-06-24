<?php

namespace App\Enums;

enum PaymentStatusEnum: int
{
    case PENDING = 0;
    case PAID = 1;
    case FAILED = 2;
    case REFUNDED = 3;

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::PAID => 'Paid',
            self::FAILED => 'Failed',
            self::REFUNDED => 'Refunded',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => '#FFA500',    // Orange
            self::PAID => '#228B22',       // Forest Green
            self::FAILED => '#DC143C',     // Crimson
            self::REFUNDED => '#708090',   // Slate Gray
        };
    }

    public static function fromString(string $status): ?self
    {
        return match($status) {
            'pending' => self::PENDING,
            'paid' => self::PAID,
            'failed' => self::FAILED,
            'refunded' => self::REFUNDED,
            default => null,
        };
    }

    /**
     * Get all enum values as array for validation
     */
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    /**
     * Get all string names for validation
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
            self::PAID => 'paid',
            self::FAILED => 'failed',
            self::REFUNDED => 'refunded',
        };
    }

    /**
     * Get all string values for API
     */
    public static function strings(): array
    {
        return ['pending', 'paid', 'failed', 'refunded'];
    }


} 