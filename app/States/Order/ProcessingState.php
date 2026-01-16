<?php
/**
 * Author: Chai Hao Lun
 */
namespace App\States\Order;

use App\Models\{Order, Book};
use Illuminate\Support\Facades\DB;

class ProcessingState extends AbstractOrderState
{
    public function ship(): Order
    {
        // mark shipped_date if exists
        if ($this->order->shipment && !$this->order->shipment->shipped_date) {
            $this->order->shipment->update(['shipped_date' => now()]);
        }
        return $this->transition(Order::STATUS_SHIPPED);
    }

    public function cancel(): Order
    {
        // Auto-refund + restore stock
        return DB::transaction(function () {
            // refund transaction
            $this->order->transactions()->create([
                'transaction_date' => now(),
                'amount' => $this->order->total_amount,
                'transaction_type' => 'Refund',
            ]);

            // restore stock
            foreach ($this->order->items as $it) {
                $book = Book::lockForUpdate()->find($it->book_id);
                if ($book) {
                    $book->stock += $it->quantity;
                    $book->save();
                    DB::table('stock_movements')->insert([
                        'book_id' => $book->id,
                        'user_id' => $this->order->user_id,
                        'type' => 'adjustment',
                        'quantity_change' => $it->quantity,
                        'reason' => 'Refund for Order #'.$this->order->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            return $this->transition(Order::STATUS_CANCELLED);
        });
    }
}
