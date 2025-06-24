<?php

namespace App\Enums;

enum OrderChannelEnum: int
{
    case WEB = 0;
    case MOBILE = 1;
    case TELEGRAM = 2;
    case WHATSAPP = 3;
    case PHONE = 4;
    case POS = 5;

    public function label(): string
    {
        return match($this) {
            self::WEB => 'Web',
            self::MOBILE => 'Mobile App',
            self::TELEGRAM => 'Telegram Bot',
            self::WHATSAPP => 'WhatsApp',
            self::PHONE => 'Phone Order',
            self::POS => 'Point of Sale',
        };
    }

    public static function fromString(string $channel): ?self
    {
        return match($channel) {
            'web' => self::WEB,
            'mobile' => self::MOBILE,
            'telegram' => self::TELEGRAM,
            'whatsapp' => self::WHATSAPP,
            'phone' => self::PHONE,
            'pos' => self::POS,
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
            self::WEB => 'web',
            self::MOBILE => 'mobile',
            self::TELEGRAM => 'telegram',
            self::WHATSAPP => 'whatsapp',
            self::PHONE => 'phone',
            self::POS => 'pos',
        };
    }

    /**
     * Get all string values for API
     */
    public static function strings(): array
    {
        return ['web', 'mobile', 'telegram', 'whatsapp', 'phone', 'pos'];
    }
} 