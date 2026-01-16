<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'book_id'    => $this->book_id,
            'book_title' => optional($this->book)->title,
            'quantity'   => (int) $this->quantity,
            'unit_price' => (float) ($this->book->price ?? 0),
            'subtotal'   => (float) (($this->book->price ?? 0) * $this->quantity),
            'added_at'   => optional($this->added_at)->toDateTimeString(),
        ];
    }
}
