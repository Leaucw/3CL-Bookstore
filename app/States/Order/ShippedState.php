<?php
/**
 * Author: Chai Hao Lun
 */
namespace App\States\Order;

use App\Models\Order;

class ShippedState extends AbstractOrderState
{
    public function arrive(): Order
    {
        if ($this->order->shipment && !$this->order->shipment->delivery_date) {
            $this->order->shipment->update(['delivery_date' => now()]);
        }
        return $this->transition(Order::STATUS_ARRIVED);
    }

    public function cancel(): Order
    {
        // Business rule: Usually you can't cancel after shipment; keep blocked or allow if you want
        return parent::cancel(); // throws
    }
}
