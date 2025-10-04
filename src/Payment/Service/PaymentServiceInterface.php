<?php

declare(strict_types=1);

namespace App\Payment\Service;

use App\ValueObject\Money;

interface PaymentServiceInterface
{
    /**
     * Executes a payment for the given amount using the specified processor.
     *
     * Supported processors (case-insensitive):
     *   - 'paypal' — uses PaypalPaymentProcessor and expects cents as integer.
     *   - 'stripe' — uses StripePaymentProcessor and expects euros as float.
     *
     * @param Money  $money          amount to charge
     * @param string $processorAlias payment processor to use (case-insensitive)
     *
     * @throws \InvalidArgumentException if the processor is unknown
     * @throws \RuntimeException         If the payment fails (e.g., Stripe returns false).
     */
    public function pay(Money $money, string $processorAlias): void;
}
