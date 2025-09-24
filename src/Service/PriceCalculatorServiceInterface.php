<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use App\Entity\Coupon;
use App\ValueObject\Money;

interface PriceCalculatorServiceInterface
{
    /**
     * Calculate the final price of a product, taking into account a coupon and tax.
     *
     * @param Product $product
     * @param string $taxNumber
     * @param Coupon|null $coupon
     *
     * @return Money
     *
     * @throws \InvalidArgumentException if the tax number is invalid
     */
    public function calculate(Product $product, string $taxNumber, ?Coupon $coupon = null): Money;
}
