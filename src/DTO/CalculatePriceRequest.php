<?php

declare(strict_types=1);

namespace App\DTO;

use App\Validator\Constraints as AppAssert;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    required: ['product', 'taxNumber']
)]
final class CalculatePriceRequest implements RequestDtoInterface
{
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\Positive]
    #[OA\Property(description: 'ID of the product', type: 'integer', example: 123)]
    public int $product;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[AppAssert\TaxNumber]
    #[OA\Property(description: 'Tax number of the customer', type: 'string', example: 'DE123456789')]
    public string $taxNumber;

    #[Assert\Type('string')]
    #[Assert\Length(max: 100)]
    #[OA\Property(description: 'Optional coupon code for discount', type: 'string', example: 'SUMMER2025', nullable: true)]
    public ?string $couponCode = null;
}
