<?php

declare(strict_types=1);

namespace App\Payment\Contract;

use App\ValueObject\Money;

interface PaymentProcessorInterface
{
    /**
     * @throws \Throwable on payment failure
     */
    public function pay(Money $money): void;
}
