<?php

declare(strict_types=1);

namespace App\Payment\Adapter;

use App\Payment\Attribute\PaymentProcessor;
use App\Payment\Contract\PaymentProcessorInterface;
use App\Payment\Enum\PaymentProcessorType;
use App\Payment\Exception\PaymentException;
use App\ValueObject\Money;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

/** @psalm-suppress UnusedClass */
#[PaymentProcessor(PaymentProcessorType::STRIPE)]
readonly class StripeProcessor implements PaymentProcessorInterface
{
    public function __construct(private StripePaymentProcessor $stripe)
    {
    }

    #[\Override]
    public function pay(Money $money): void
    {
        try {
            $this->stripe->processPayment($money->getEuros())
                ?: throw new PaymentException('Stripe payment failed');
        } catch (\Throwable $e) {
            throw new PaymentException('Stripe payment failed: '.$e->getMessage());
        }
    }
}
