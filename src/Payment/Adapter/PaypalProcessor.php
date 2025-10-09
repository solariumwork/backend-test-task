<?php

declare(strict_types=1);

namespace App\Payment\Adapter;

use App\Payment\Attribute\PaymentProcessor;
use App\Payment\Contract\PaymentProcessorInterface;
use App\Payment\Enum\PaymentProcessorType;
use App\Payment\Exception\PaymentException;
use App\ValueObject\Money;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;

/** @psalm-suppress UnusedClass */
#[PaymentProcessor(PaymentProcessorType::PAYPAL)]
readonly class PaypalProcessor implements PaymentProcessorInterface
{
    public function __construct(private PaypalPaymentProcessor $paypal)
    {
    }

    #[\Override]
    public function pay(Money $money): void
    {
        try {
            $this->paypal->pay($money->getCents());
        } catch (\Throwable $e) {
            throw new PaymentException('Paypal payment failed: '.$e->getMessage());
        }
    }
}
