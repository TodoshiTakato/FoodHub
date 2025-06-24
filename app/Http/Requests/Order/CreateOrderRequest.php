<?php

namespace App\Http\Requests\Order;

use App\Enums\OrderChannelEnum;
use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'restaurant_id' => 'required|exists:restaurants,id',
            'channel' => 'required|in:' . implode(',', OrderChannelEnum::strings()),
            'customer_info' => 'required|array',
            'customer_info.name' => 'required|string|max:255',
            'customer_info.phone' => 'required|string|max:20',
            'customer_info.email' => 'nullable|email|max:255',
            'delivery_info' => 'required|array',
            'delivery_info.type' => 'required|in:delivery,pickup',
            'delivery_info.address' => 'required_if:delivery_info.type,delivery|array',
            'delivery_info.scheduled_at' => 'nullable|date|after:now',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.modifiers' => 'nullable|array',
            'items.*.special_instructions' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'special_instructions' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'restaurant_id.required' => 'Restaurant is required',
            'restaurant_id.exists' => 'Selected restaurant does not exist',
            'channel.required' => 'Order channel is required',
            'channel.in' => 'Invalid order channel',
            'customer_info.required' => 'Customer information is required',
            'customer_info.name.required' => 'Customer name is required',
            'customer_info.phone.required' => 'Customer phone is required',
            'delivery_info.required' => 'Delivery information is required',
            'delivery_info.type.required' => 'Delivery type is required',
            'delivery_info.type.in' => 'Delivery type must be either delivery or pickup',
            'delivery_info.address.required_if' => 'Address is required for delivery orders',
            'items.required' => 'Order items are required',
            'items.min' => 'Order must contain at least one item',
            'items.*.product_id.required' => 'Product is required for each item',
            'items.*.product_id.exists' => 'Selected product does not exist',
            'items.*.quantity.required' => 'Quantity is required for each item',
            'items.*.quantity.min' => 'Quantity must be at least 1',
        ];
    }
} 