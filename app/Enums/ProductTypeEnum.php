<?php

namespace App\Enums;

enum ProductTypeEnum: int
{
    case SIMPLE = 0;
    case COMBO = 1;
    case MODIFIER = 2;

    public function label(): string
    {
        return match($this) {
            self::SIMPLE => 'Simple Product',
            self::COMBO => 'Combo Product',
            self::MODIFIER => 'Modifier',
        };
    }

    public static function fromString(string $type): ?self
    {
        return match($type) {
            'simple' => self::SIMPLE,
            'combo' => self::COMBO,
            'modifier' => self::MODIFIER,
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
            self::SIMPLE => 'simple',
            self::COMBO => 'combo',
            self::MODIFIER => 'modifier',
        };
    }

    /**
     * Get all string values for API
     */
    public static function strings(): array
    {
        return ['simple', 'combo', 'modifier'];
    }
} 