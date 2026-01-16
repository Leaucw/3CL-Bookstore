<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateBookRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();
        return $auth->check();
    }

    public function rules(): array
    {
        $id = $this->route('book')?->id; // null on create

        return [
            'title'          => ['required','string','max:255'],
            'author'         => ['nullable','string','max:255'],
            'isbn'           => ['nullable','string','max:50','unique:books,isbn,'.($id ?? 'NULL').',id'],
            'genre'    => ['nullable','string','max:100'],
            'description'    => ['nullable','string'],
            'price'          => ['nullable','numeric','min:0'],
            'stock'          => ['nullable','integer','min:0'],
            'cover'          => ['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
            'category_id' => ['nullable','integer','exists:categories,id'],
            'tag_ids'        => ['nullable','array'],
            'tag_ids.*'      => ['integer','exists:tags,id'],
        ];
    }
}
