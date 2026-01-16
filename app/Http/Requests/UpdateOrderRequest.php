<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Controller/policy will still enforce ownership; allow request to pass validation
        return true;
    }

    public function rules(): array
    {
        return [
            // Only allow changing shipping_address (while Pending/Processing in controller)
            'shipping_address' => ['sometimes','required','string','max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'shipping_address.required' => 'Please provide a shipping address.',
        ];
    }
}
