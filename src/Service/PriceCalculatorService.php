<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use App\Entity\Coupon;
use App\ValueObject\Money;

class PriceCalculatorService
{
    public function calculate(Product $product, string $taxNumber, ?Coupon $coupon = null): Money
    {
        $taxRate = $this->getTaxRate($taxNumber);
        if ($taxRate === null) {
            throw new \InvalidArgumentException('Invalid tax number');
        }

        $totalCents = $product->getPrice()->getCents();

        if ($coupon instanceof Coupon) {
            $discount = 0;

            if ($coupon->getType() === Coupon::TYPE_PERCENT) {
                $discount = (int) round((float) $totalCents * ((float) $coupon->getValue() / 100));
            } elseif ($coupon->getType() === Coupon::TYPE_FIXED) {
                $discount = $coupon->getValue();
            }

            $totalCents -= $discount;
            $totalCents = max(0, $totalCents);
        }

        $totalCents = (int) round($totalCents * (1 + $taxRate));

        return new Money($totalCents, $product->getPrice()->getCurrency());
    }

    private function getTaxRate(string $taxNumber): ?float
    {
        $taxNumber = trim($taxNumber);

        return match (true) {
            preg_match('/^DE\d{9}$/', $taxNumber) === 1 => 0.19,
            preg_match('/^IT\d{11}$/', $taxNumber) === 1 => 0.22,
            preg_match('/^FR[A-Z]{2}\d{9}$/', $taxNumber) === 1 => 0.20,
            preg_match('/^GR\d{9}$/', $taxNumber) === 1 => 0.24,
            default => null,
        };
    }
}
