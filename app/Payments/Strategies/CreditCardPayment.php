<?php
/**
 * Author: Chai Hao Lun
 */
namespace App\Payments\Strategies;

use App\Payments\Contracts\PaymentStrategy;
use App\Models\{Order, TransactionHistory};
use Illuminate\Http\Request;

class CreditCardPayment implements PaymentStrategy
{
    public function key(): string { return 'Credit Card'; }

    public function validate(Request $request): void
    {
        $request->validate([
            'card_number' => ['required','digits_between:12,19'],
            'exp_month'   => ['required','integer','between:1,12'],
            'exp_year'    => ['required','integer','min:'.date('Y')],
            'cvv'         => ['required','digits_between:3,4'],
            'card_name'   => ['required','string','max:100'],
        ]);
    }

    public function charge(Order $order, float $amount, array $data): void
    {
        TransactionHistory::create([
            'order_id'         => $order->id,
            'transaction_date' => now(),
            'amount'           => $amount,
            'transaction_type' => 'Payment',
        ]);
    }
}
