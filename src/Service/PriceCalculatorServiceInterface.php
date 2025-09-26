<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Coupon;
use App\Entity\Product;
use App\ValueObject\Money;

interface PriceCalculatorServiceInterface
{
    /**
     * Calculate total price including discount and tax.
     *
     * @param Product $product
     * @param string $taxNumber Valid tax number
     * @param Coupon|null $coupon Optional coupon
     * @return Money
     *
     * @throws \InvalidArgumentException if the tax number is invalid
     */
    public function calculateTotalAmount(Product $product, string $taxNumber, ?Coupon $coupon = null): Money;
}
