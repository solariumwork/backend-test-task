<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enum\PaymentProcessorType;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as AppAssert;

#[OA\Schema(
    required: ['product', 'taxNumber', 'paymentProcessor']
)]
final class PurchaseRequest implements RequestDtoInterface
{
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\Positive]
    #[OA\Property(description: 'ID of the product', type: 'integer', example: 1)]
    public int $product;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[AppAssert\TaxNumber]
    #[OA\Property(description: 'Tax number of the customer', type: 'string', example: 'DE123456789')]
    public string $taxNumber;

    #[Assert\Type('string')]
    #[Assert\Length(max: 30)]
    #[OA\Property(description: 'Optional coupon code for discount', type: 'string', example: 'SUMMER2025', nullable: true)]
    public ?string $couponCode = null;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Choice(choices: PaymentProcessorType::CHOICES)]
    #[OA\Property(description: 'Payment processor to use (case-insensitive)', type: 'string', example: 'paypal')]
    public string $paymentProcessor;
}
