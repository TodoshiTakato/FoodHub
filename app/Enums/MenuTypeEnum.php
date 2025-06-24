<?php

namespace App\Enums;

enum MenuTypeEnum: int
{
    case MAIN = 0;
    case BREAKFAST = 1;
    case LUNCH = 2;
    case DINNER = 3;
    case DRINKS = 4;
    case SPECIAL = 5;

    public function label(): string
    {
        return match($this) {
            self::MAIN => 'Main Menu',
            self::BREAKFAST => 'Breakfast',
            self::LUNCH => 'Lunch',
            self::DINNER => 'Dinner',
            self::DRINKS => 'Drinks',
            self::SPECIAL => 'Special',
        };
    }

    public static function fromString(string $type): ?self
    {
        return match($type) {
            'main' => self::MAIN,
            'breakfast' => self::BREAKFAST,
            'lunch' => self::LUNCH,
            'dinner' => self::DINNER,
            'drinks' => self::DRINKS,
            'special' => self::SPECIAL,
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
            self::MAIN => 'main',
            self::BREAKFAST => 'breakfast',
            self::LUNCH => 'lunch',
            self::DINNER => 'dinner',
            self::DRINKS => 'drinks',
            self::SPECIAL => 'special',
        };
    }

    /**
     * Get all string values for API
     */
    public static function strings(): array
    {
        return ['main', 'breakfast', 'lunch', 'dinner', 'drinks', 'special'];
    }
} 