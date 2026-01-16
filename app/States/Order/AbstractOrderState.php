<?php
/**
 * Author: Chai Hao Lun
 */
namespace App\States\Order;

use App\Models\Order;
use Illuminate\Validation\UnauthorizedException;

abstract class AbstractOrderState implements OrderState
{
    public function __construct(protected Order $order) {}

    public function name(): string { return $this->order->status; }

    protected function transition(string $newStatus): Order
    {
        $this->order->status = $newStatus;
        $this->order->save();
        return $this->order;
    }

    // Default: not allowed; concrete states override allowed transitions
    public function ship()    { throw new UnauthorizedException('Cannot ship from '.$this->name()); }
    public function arrive()  { throw new UnauthorizedException('Cannot mark arrived from '.$this->name()); }
    public function complete(){ throw new UnauthorizedException('Cannot complete from '.$this->name()); }
    public function cancel()  { throw new UnauthorizedException('Cannot cancel from '.$this->name()); }
}
