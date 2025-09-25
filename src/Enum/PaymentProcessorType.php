<?php

declare(strict_types=1);

namespace App\Enum;

enum PaymentProcessorType: string
{
    case PAYPAL = 'paypal';
    case STRIPE = 'stripe';

    public const array CHOICES = [
        self::PAYPAL->value,
        self::STRIPE->value,
    ];
}
