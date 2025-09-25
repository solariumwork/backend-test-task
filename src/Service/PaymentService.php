<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\PaymentProcessorType;
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
        $processor = strtolower($processor);

        match ($processor) {
            PaymentProcessorType::PAYPAL->value => $this->paypal->pay($money->getCents()),
            PaymentProcessorType::STRIPE->value => $this->stripe->processPayment($money->getEuros()),
            default => throw new \InvalidArgumentException('Unknown payment processor'),
        };
    }
}
