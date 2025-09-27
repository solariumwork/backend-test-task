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
     * @param string      $taxNumber Valid tax number
     * @param Coupon|null $coupon    Optional coupon
     *
     * @throws \InvalidArgumentException if the tax number is invalid
     */
    public function calculateTotalAmount(Product $product, string $taxNumber, ?Coupon $coupon = null): Money;
}
