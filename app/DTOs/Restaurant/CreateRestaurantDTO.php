<?php

namespace App\DTOs\Restaurant;

use App\Enums\StatusEnum;
use Illuminate\Http\Request;

class CreateRestaurantDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        public readonly ?array $address = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly ?string $timezone = null,
        public readonly ?string $currency = null,
        public readonly ?array $languages = null,
        public readonly ?array $business_hours = null,
        public readonly ?array $delivery_zones = null,
        public readonly ?array $settings = null,
    ) {}

    /**
     * Create DTO from Request
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            description: $request->validated('description'),
            phone: $request->validated('phone'),
            email: $request->validated('email'),
            address: $request->validated('address'),
            latitude: $request->validated('latitude'),
            longitude: $request->validated('longitude'),
            timezone: $request->validated('timezone'),
            currency: $request->validated('currency'),
            languages: $request->validated('languages'),
            business_hours: $request->validated('business_hours'),
            delivery_zones: $request->validated('delivery_zones'),
            settings: $request->validated('settings'),
        );
    }

    /**
     * Convert to array for Restaurant creation
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => \Str::slug($this->name),
            'description' => $this->description,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'timezone' => $this->timezone,
            'currency' => $this->currency,
            'languages' => $this->languages,
            'business_hours' => $this->business_hours,
            'delivery_zones' => $this->delivery_zones,
            'settings' => $this->settings,
            'status' => StatusEnum::ACTIVE->value,  // Используем ->value для БД
        ];
    }
} 