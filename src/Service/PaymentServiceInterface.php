<?php

declare(strict_types=1);

namespace App\Service;

use App\ValueObject\Money;

interface PaymentServiceInterface
{
    /**
     * Execute a payment with the specified amount using the selected processor.
     *
     * @param Money $money
     * @param string $processor 'paypal' or 'stripe'
     *
     * @throws \InvalidArgumentException if the processor is unknown
     */
    public function pay(Money $money, string $processor): void;
}
