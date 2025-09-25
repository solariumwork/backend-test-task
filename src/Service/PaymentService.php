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
        match (strtolower($processor)) {
            PaymentProcessorType::PAYPAL->value => $this->paypal->pay($money->getCents()),
            PaymentProcessorType::STRIPE->value => $this->processStripe($money),
            default => throw new \InvalidArgumentException('Unknown payment processor'),
        };
    }

    private function processStripe(Money $money): void
    {
        if (!$this->stripe->processPayment($money->getEuros())) {
            throw new \RuntimeException('Stripe payment failed');
        }
    }
}
