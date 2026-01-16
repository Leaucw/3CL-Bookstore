<?php

/**
 * Author: Chai Hao Lun
 */

namespace App\Payments\Contracts;

use App\Models\Order;
use Illuminate\Http\Request;

interface PaymentStrategy
{
    /** Unique key shown/posted from the form (e.g. "E-Wallet", "Credit Card", "Bank Transfer") */
    public function key(): string;

    /** Validate request fields needed for this payment method. Throw ValidationException on fail. */
    public function validate(Request $request): void;

    /** Perform the "charge" (simulated) and record TransactionHistory. */
    public function charge(Order $order, float $amount, array $data): void;
}
