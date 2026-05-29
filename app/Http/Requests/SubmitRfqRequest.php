<?php

namespace App\Http\Requests;

use App\Enums\DeliveryMethodEnum;
use App\Enums\QuantityEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitRfqRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public submission
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'whatsapp_number' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'product_name' => ['required', 'string', 'max:255'],
            'specifications' => ['required', 'string'],
            'quantity' => ['required', Rule::enum(QuantityEnum::class)],
            'delivery_method' => ['required', Rule::enum(DeliveryMethodEnum::class)],
            'target_price' => ['nullable', 'string', 'max:255'],
            'additional_requirements' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:4096'],
        ];
    }
}
