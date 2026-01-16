<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartAddRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'book_id'  => ['required','exists:books,id'],
            'quantity' => ['required','integer','min:1'],
        ];
    }
}
