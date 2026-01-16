<?php
/**
 * Author: Chai Hao Lun
 */
namespace App\States\Order;

use App\Models\Order;

interface OrderState
{
    public function name(): string;

    /** @return Order */
    public function ship();
    /** @return Order */
    public function arrive();
    /** @return Order */
    public function complete();
    /** @return Order */
    public function cancel();
}
