<?php

namespace App\Http\Requests\Order;

use App\Enums\OrderStatusEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
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
            'status' => 'required|in:' . implode(',', OrderStatusEnum::strings()),
            'cancellation_reason' => 'required_if:status,cancelled|string|max:500',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Order status is required',
            'status.in' => 'Invalid order status',
            'cancellation_reason.required_if' => 'Cancellation reason is required when cancelling order',
            'cancellation_reason.max' => 'Cancellation reason must not exceed 500 characters',
        ];
    }
} 