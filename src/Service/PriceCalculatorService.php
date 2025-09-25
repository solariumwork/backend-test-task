<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Coupon;
use App\Entity\Product;
use App\Enum\TaxRate;
use App\ValueObject\Money;

class PriceCalculatorService implements PriceCalculatorServiceInterface
{
    public function calculate(Product $product, string $taxNumber, ?Coupon $coupon = null): Money
    {
        $totalCents = $this->calculateFinalCents(
            $product->getPrice()->getCents(),
            $coupon,
            $taxNumber
        );

        return new Money($totalCents, $product->getPrice()->getCurrency());
    }

    private function calculateFinalCents(int $priceCents, ?Coupon $coupon, string $taxNumber): int
    {
        $priceCentsAfterDiscount = $coupon
            ? $this->applyDiscount($priceCents, $coupon)
            : $priceCents;
        $taxRate = TaxRate::fromTaxNumber($taxNumber);

        return $this->applyTax($priceCentsAfterDiscount, $taxRate);
    }

    private function applyDiscount(int $priceCents, Coupon $coupon): int
    {
        return max(0, $priceCents - $this->calculateDiscount($priceCents, $coupon));
    }

    private function calculateDiscount(int $priceCents, Coupon $coupon): int
    {
        return match ($coupon->getType()) {
            Coupon::TYPE_PERCENT => (int) round($priceCents * ($coupon->getValue() / 100)),
            Coupon::TYPE_FIXED => $coupon->getValue(),
            default => 0,
        };
    }

    private function applyTax(int $priceCents, float $taxRate): int
    {
        return (int) round($priceCents * (1 + $taxRate));
    }
}
