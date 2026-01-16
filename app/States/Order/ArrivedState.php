<?php
/**
 * Author: Chai Hao Lun
 */
namespace App\States\Order;

use App\Models\Order;

class ArrivedState extends AbstractOrderState
{
    public function complete(): Order
    {
        return $this->transition(Order::STATUS_COMPLETED);
    }
}
