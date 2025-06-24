<?php

namespace App\Enums;

enum StatusEnum: int
{
    case ACTIVE = 0;
    case INACTIVE = 1;
    case SUSPENDED = 2;

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::SUSPENDED => 'Suspended',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::ACTIVE => '#228B22',      // Forest Green
            self::INACTIVE => '#708090',    // Slate Gray
            self::SUSPENDED => '#DC143C',   // Crimson
        };
    }

    public static function fromString(string $status): ?self
    {
        return match($status) {
            'active' => self::ACTIVE,
            'inactive' => self::INACTIVE,
            'suspended' => self::SUSPENDED,
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
     * Convert enum to API string format
     */
    public function toString(): string
    {
        return match($this) {
            self::ACTIVE => 'active',
            self::INACTIVE => 'inactive',
            self::SUSPENDED => 'suspended',
        };
    }

    /**
     * Get all string values for API
     */
    public static function strings(): array
    {
        return ['active', 'inactive', 'suspended'];
    }
} 