<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PurchaseRequest',
    description: 'DTO for making a purchase'
)]
final class PurchaseRequest implements RequestDtoInterface
{
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[OA\Property(
        description: 'Product ID to purchase',
        type: 'integer',
        example: 1
    )]
    public int $product;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[OA\Property(
        description: 'Tax number for calculation',
        type: 'string',
        example: 'DE123456789'
    )]
    public string $taxNumber;

    #[Assert\Type('string')]
    #[OA\Property(
        description: 'Optional coupon code',
        type: 'string',
        example: 'COUPON10',
        nullable: true
    )]
    public ?string $couponCode = null;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[OA\Property(
        description: 'Payment processor (e.g., paypal, stripe)',
        type: 'string',
        example: 'paypal'
    )]
    public string $paymentProcessor;
}
