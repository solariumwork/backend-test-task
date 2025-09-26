<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Coupon;
use App\Entity\Product;
use App\Enum\TaxRate;
use App\ValueObject\Money;

class PriceCalculatorService implements PriceCalculatorServiceInterface
{
    #[\Override]
    public function calculateTotalAmount(Product $product, string $taxNumber, ?Coupon $coupon = null): Money
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
        $discountedPrice = $coupon ? $this->applyDiscount($priceCents, $coupon) : $priceCents;
        $taxRate = TaxRate::fromTaxNumber($taxNumber);

        return $this->applyTax($discountedPrice, $taxRate);
    }

    private function applyDiscount(int $priceCents, Coupon $coupon): int
    {
        return max(0, $priceCents - $this->calculateDiscount($priceCents, $coupon));
    }

    /*
     * Example calculation:
     *
     * Percent coupon:
     * $priceCents         = 10000        // 100 € in cents
     * $coupon->getValue() = 15          // 15% discount
     * $discountRate       = bcdiv((string)$coupon->getValue(), '100', 4) // 0.15
     * $discountCents      = bcmul((string)$priceCents, $discountRate, 0) // 10000 * 0.15 = 1500 cents (15 €)
     *
     * Fixed coupon:
     * $coupon->getValue() = 2000        // 20 € fixed discount in cents
     * $discountCents      = 2000
     */
    private function calculateDiscount(int $priceCents, Coupon $coupon): int
    {
        return match ($coupon->getType()) {
            Coupon::TYPE_PERCENT => (function() use ($priceCents, $coupon): int {
                $discountRate = bcdiv((string)$coupon->getValue(), '100', 4);
                $discountCents = bcmul((string)$priceCents, $discountRate, 0);
                return (int) $discountCents;
            })(),
            Coupon::TYPE_FIXED => $coupon->getValue(),
            default => 0,
        };
    }


    /*
     * Example calculation:
     * $priceCentsAsString = "10000"   // 100 € in cents
     * $taxRateAsString    = "0.19"    // 19% tax
     * $taxMultiplier      = bcadd('1', $taxRateAsString, 4) // 1 + 0.19 = 1.19
     * $totalCentsWithTax  = bcmul($priceCentsAsString, $taxMultiplier, 0) // 10000 * 1.19 = 11900 cents (119 €)
     */
    private function applyTax(int $priceCents, float $taxRate): int
    {
        $priceCentsAsString = (string) $priceCents;
        $taxRateAsString = (string) $taxRate;

        $taxMultiplier = bcadd('1', $taxRateAsString, 4);
        $totalCentsWithTax = bcmul($priceCentsAsString, $taxMultiplier, 4);

        return (int) round((float)$totalCentsWithTax, 0, PHP_ROUND_HALF_UP);
    }
}
