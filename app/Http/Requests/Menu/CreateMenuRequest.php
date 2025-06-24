<?php

namespace App\Http\Requests\Menu;

use App\Enums\MenuTypeEnum;
use Illuminate\Foundation\Http\FormRequest;

class CreateMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'restaurant_id' => 'required|exists:restaurants,id',
            'name' => 'required|array',
            'name.*' => 'required|string|max:255',
            'description' => 'nullable|array',
            'description.*' => 'nullable|string|max:1000',
            'type' => 'required|in:' . implode(',', MenuTypeEnum::strings()),
            'channels' => 'required|array',
            'channels.*' => 'in:web,mobile,telegram,whatsapp,phone,pos',
            'availability_hours' => 'nullable|array',
            'sort_order' => 'nullable|integer|min:0',
            'settings' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'restaurant_id.required' => 'Restaurant is required',
            'name.required' => 'Menu name is required',
            'type.required' => 'Menu type is required',
            'channels.required' => 'Menu channels are required',
        ];
    }
} 