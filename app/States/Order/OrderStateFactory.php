<?php
/**
 * Author: Chai Hao Lun
 */
namespace App\States\Order;

use App\Models\Order;

class OrderStateFactory
{
    public static function for(Order $order): OrderState
    {
        return match ($order->status) {
            Order::STATUS_PROCESSING => new ProcessingState($order),
            Order::STATUS_SHIPPED    => new ShippedState($order),
            Order::STATUS_ARRIVED    => new ArrivedState($order),
            Order::STATUS_COMPLETED  => new CompletedState($order),
            Order::STATUS_CANCELLED  => new CancelledState($order),
            default => new ProcessingState($order), // fallback
        };
    }
}
