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
        $totalCents = $this->calculateFinalCents($product->getPrice()->getCents(), $coupon, $taxNumber);

        return new Money($totalCents, $product->getPrice()->getCurrency());
    }

    private function calculateFinalCents(int $priceCents, ?Coupon $coupon, string $taxNumber): int
    {
        $priceCentsAfterDiscount = $coupon ? $this->applyDiscount($priceCents, $coupon) : $priceCents;

        return $this->applyTax($priceCentsAfterDiscount, $this->resolveTaxRate($taxNumber));
    }

    private function applyDiscount(int $priceCents, Coupon $coupon): int
    {
        $discount = match ($coupon->getType()) {
            Coupon::TYPE_PERCENT => (int) round($priceCents * ($coupon->getValue() / 100)),
            Coupon::TYPE_FIXED   => $coupon->getValue(),
            default               => 0,
        };

        return max(0, $priceCents - $discount);
    }

    private function applyTax(int $priceCents, float $taxRate): int
    {
        return (int) round($priceCents * (1 + $taxRate));
    }

    private function resolveTaxRate(string $taxNumber): float
    {
        return match (true) {
            preg_match('/^DE\d{9}$/', $taxNumber) === 1 => 0.19,
            preg_match('/^IT\d{11}$/', $taxNumber) === 1 => 0.22,
            preg_match('/^FR[A-Z]{2}\d{9}$/', $taxNumber) === 1 => 0.20,
            preg_match('/^GR\d{9}$/', $taxNumber) === 1 => 0.24,
            default => throw new \InvalidArgumentException('Invalid tax number'),
        };
    }
}
