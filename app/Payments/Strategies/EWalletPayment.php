<?php
/**
 * Author: Chai Hao Lun
 */
namespace App\Payments\Strategies;

use App\Payments\Contracts\PaymentStrategy;
use App\Models\{Order, TransactionHistory};
use Illuminate\Http\Request;

class EWalletPayment implements PaymentStrategy
{
    public function key(): string { return 'E-Wallet'; }

    public function validate(Request $request): void
    {
        $request->validate([
            'wallet_provider' => ['required','string','max:100'],
            'wallet_id'       => ['required','string','max:100'],
        ]);
    }

    public function charge(Order $order, float $amount, array $data): void
    {
        // Simulate gateway success and record a payment transaction
        TransactionHistory::create([
            'order_id'         => $order->id,
            'transaction_date' => now(),
            'amount'           => $amount,
            'transaction_type' => 'Payment',
        ]);
    }
}
