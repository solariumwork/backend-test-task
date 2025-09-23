<?php

declare(strict_types=1);

namespace App\Service;

use App\ValueObject\Money;
use Systemeio\TestForCandidates\PaypalPaymentProcessor;
use Systemeio\TestForCandidates\StripePaymentProcessor;

final class PaymentService
{
    public function pay(Money $money, string $processor): void
    {
        match (strtolower($processor)) {
            'paypal' => PaypalPaymentProcessor::pay($money->getCents()),
            'stripe' => StripePaymentProcessor::processPayment($money->getCents()),
            default => throw new \InvalidArgumentException('Unknown payment processor'),
        };
    }
}
