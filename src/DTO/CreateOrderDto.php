<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Coupon;
use App\Entity\Product;
use App\ValueObject\Money;

/** @psalm-suppress PossiblyUnusedProperty */
final readonly class CreateOrderDto
{
    public function __construct(
        public Product $product,
        public Money $total,
        public string $taxNumber,
        public string $paymentProcessor,
        public ?Coupon $coupon = null,
    ) {
    }

    public static function fromPurchaseRequest(
        PurchaseRequest $request,
        Product $product,
        Money $total,
        ?Coupon $coupon = null,
    ): self {
        return new self(
            product: $product,
            total: $total,
            taxNumber: $request->taxNumber,
            paymentProcessor: $request->paymentProcessor,
            coupon: $coupon
        );
    }
}
