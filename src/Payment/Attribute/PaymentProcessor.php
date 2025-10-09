<?php

declare(strict_types=1);

namespace App\Payment\Attribute;

use App\Payment\Enum\PaymentProcessorType;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class PaymentProcessor
{
    public function __construct(public PaymentProcessorType $type)
    {
    }
}
