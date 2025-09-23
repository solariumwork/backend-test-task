<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class PurchaseRequest implements RequestDtoInterface
{
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    public int $product;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $taxNumber;

    #[Assert\Type('string')]
    public ?string $couponCode = null;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $paymentProcessor;
}
