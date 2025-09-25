<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

final class CalculatePriceRequest implements RequestDtoInterface
{
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\Positive]
    #[OA\Property(
        description: 'ID of the product',
        type: 'integer',
        example: 123
    )]
    public int $product;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(max: 20)]
    #[OA\Property(
        description: 'Tax number of the customer',
        type: 'string',
        example: 'DE123456789'
    )]
    public string $taxNumber;

    #[Assert\Type('string')]
    #[Assert\Length(max: 30)]
    #[OA\Property(
        description: 'Optional coupon code for discount',
        type: 'string',
        example: 'SUMMER2025',
        nullable: true
    )]
    public ?string $couponCode = null;
}
