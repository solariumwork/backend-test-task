<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CalculatePriceRequest',
    description: 'DTO for calculating product price'
)]
final class CalculatePriceRequest implements RequestDtoInterface
{
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[OA\Property(description: 'Product ID', type: 'integer')]
    public int $product;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[OA\Property(description: 'Tax number for calculation', type: 'string')]
    public string $taxNumber;

    #[Assert\Type('string')]
    #[OA\Property(description: 'Optional coupon code', type: 'string', nullable: true)]
    public ?string $couponCode = null;
}
