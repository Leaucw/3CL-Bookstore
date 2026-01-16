<?php
//Author: Leau Chee Way
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjustStockRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'type'     => ['required','in:restock,sale,adjustment'],
            'quantity' => ['required','integer'], // adjustment can be negative
            'reason'   => ['nullable','string','max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('type') !== 'adjustment') {
            $q = abs((int) $this->input('quantity'));
            $this->merge(['quantity' => $this->input('type') === 'restock' ? $q : -$q]);
        }
    }
}
