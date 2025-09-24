<?php

declare(strict_types=1);

namespace App\Service;

use App\ValueObject\Money;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

readonly class PaymentService implements PaymentServiceInterface
{
    public function __construct(
        private PaypalPaymentProcessor $paypal,
        private StripePaymentProcessor $stripe,
    ) {
        //
    }

    public function pay(Money $money, string $processor): void
    {
        match (strtolower($processor)) {
            'paypal' => $this->paypal->pay($money->getCents()),
            'stripe' => $this->stripe->processPayment($money->getEuros()),
            default => throw new \InvalidArgumentException('Unknown payment processor'),
        };
    }
}
