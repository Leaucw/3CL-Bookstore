<?php
/**
 * Author: Chai Hao Lun
 */
namespace App\Payments;

use InvalidArgumentException;
use App\Payments\Contracts\PaymentStrategy;
use App\Payments\Strategies\{
    EWalletPayment, CreditCardPayment, BankTransferPayment
};

class PaymentManager
{
    /** @var array<string, PaymentStrategy> */
    protected array $map;

    public function __construct(
        EWalletPayment $wallet,
        CreditCardPayment $card,
        BankTransferPayment $bank,
    ) {
        $this->map = [
            $wallet->key() => $wallet,
            $card->key()   => $card,
            $bank->key()   => $bank,
        ];
    }

    public function resolve(string $method): PaymentStrategy
    {
        if (! isset($this->map[$method])) {
            throw new InvalidArgumentException("Unsupported payment method: {$method}");
        }
        return $this->map[$method];
    }
}
