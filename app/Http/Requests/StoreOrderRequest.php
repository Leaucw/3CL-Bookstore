<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Book;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        // allow either qty or quantity in payload; normalize to qty
        $items = $this->input('items', []);
        foreach ($items as $i => $row) {
            if (!isset($row['qty']) && isset($row['quantity'])) {
                $items[$i]['qty'] = $row['quantity'];
                unset($items[$i]['quantity']);
            }
        }
        $this->merge(['items' => $items]);
    }

    public function rules(): array
    {
        $bookKey = (new Book)->getKeyName(); // 'id' (or 'book_id' if you set it)

        return [
            'shipping_address'   => ['required','string','max:2000'],

            // Optional extras to match your JSON
            'payment_method'     => ['nullable','string','max:100'],
            'shipping_method'    => ['nullable', Rule::in(['standard','express'])],
            'shipping_amount'    => ['nullable','numeric','min:0'],
            'order_note'         => ['nullable','string','max:2000'],
            'use_points'         => ['sometimes','boolean'],

            'items'              => ['required','array','min:1'],
            'items.*.book_id'    => ['required','integer', Rule::exists('books', $bookKey)],
            'items.*.qty'        => ['required','integer','min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.*.book_id.exists' => 'One or more selected books are invalid.',
        ];
    }
}
