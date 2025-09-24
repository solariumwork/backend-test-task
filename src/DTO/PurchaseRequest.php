<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

final class PurchaseRequest implements RequestDtoInterface
{
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[OA\Property(
        description: 'ID of the product',
        type: 'integer',
        example: 123
    )]
    public int $product;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[OA\Property(
        description: 'Tax number of the customer',
        type: 'string',
        example: 'DE123456789'
    )]
    public string $taxNumber;

    #[Assert\Type('string')]
    #[OA\Property(
        description: 'Optional coupon code for discount',
        type: 'string',
        example: 'SUMMER2025',
        nullable: true
    )]
    public ?string $couponCode = null;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[OA\Property(
        description: 'Payment processor to use (e.g., Stripe, PayPal)',
        type: 'string',
        example: 'Stripe'
    )]
    public string $paymentProcessor;
}
