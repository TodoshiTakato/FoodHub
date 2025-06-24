<?php

namespace App\DTOs\Auth;

use Illuminate\Http\Request;

class RegisterUserDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly ?string $phone = null,
        public readonly ?int $restaurant_id = null,
    ) {}

    /**
     * Create DTO from Request
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
            phone: $request->validated('phone'),
            restaurant_id: $request->validated('restaurant_id'),
        );
    }

    /**
     * Create DTO from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            phone: $data['phone'] ?? null,
            restaurant_id: $data['restaurant_id'] ?? null,
        );
    }

    /**
     * Convert to array for User creation
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'phone' => $this->phone,
            'restaurant_id' => $this->restaurant_id,
            'status' => 'active',
        ];
    }


} 