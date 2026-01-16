<?php
/**
 * Author: Chai Hao Lun
 */
namespace App\Payments\Strategies;

use App\Payments\Contracts\PaymentStrategy;
use App\Models\{Order, TransactionHistory};
use Illuminate\Http\Request;

class BankTransferPayment implements PaymentStrategy
{
    public function key(): string { return 'Bank Transfer'; }

    public function validate(Request $request): void
    {
        $request->validate([
            'bank_name'   => ['required','string','max:120'],
            'transfer_ref'=> ['required','string','max:120'],
        ]);
    }

    public function charge(Order $order, float $amount, array $data): void
    {
        // For bank transfer you might mark as pending and confirm later.
        // For assignment simplicity, mark as paid.
        TransactionHistory::create([
            'order_id'         => $order->id,
            'transaction_date' => now(),
            'amount'           => $amount,
            'transaction_type' => 'Payment',
        ]);
    }
}
