<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Coupon;
use App\Entity\Product;
use App\Enum\TaxRate;
use App\ValueObject\Money;

final readonly class PriceCalculatorService implements PriceCalculatorServiceInterface
{
    private const int BC_SCALE = 8;

    #[\Override]
    public function calculateTotalAmount(Product $product, string $taxNumber, ?Coupon $coupon = null): Money
    {
        $priceCents = (string) $product->getPrice()->getCents();

        $totalCents = $this->calculateFinalCents(
            $priceCents,
            $coupon,
            $taxNumber
        );

        return new Money($totalCents, $product->getPrice()->getCurrency());
    }

    /**
     * Calculate the final price in cents, rounding once at the end.
     *
     * EXAMPLE FLOW:
     * 1. priceCents = "10000"
     * 2. applyDiscount -> "9400"
     * 3. applyTax with taxRate=0.24 -> "11656"
     * 4. Rounded integer = 11656
     *
     * @param numeric-string $priceCents
     */
    private function calculateFinalCents(string $priceCents, ?Coupon $coupon, string $taxNumber): int
    {
        $discountedPrice = $coupon
            ? $this->applyDiscount($priceCents, $coupon)
            : $priceCents;

        $taxRate = TaxRate::fromTaxNumber($taxNumber);
        $taxedPrice = $this->applyTax($discountedPrice, $taxRate);

        return (int) bcadd($taxedPrice, '0.5', 0);
    }

    /**
     * Apply a coupon to the price.
     *
     * EXAMPLE:
     *  - priceCents = "10000"
     *  - percent coupon 6% -> discount = 600
     *  - discounted price = 9400
     *
     * @param numeric-string $priceCents
     *
     * @return numeric-string
     */
    private function applyDiscount(string $priceCents, Coupon $coupon): string
    {
        $discount = $this->calculateDiscount($priceCents, $coupon);
        $discountedPrice = bcsub($priceCents, $discount, self::BC_SCALE);

        return bccomp($discountedPrice, '0', self::BC_SCALE) < 0 ? '0' : $discountedPrice;
    }

    /**
     * Calculate the discount amount.
     *
     * EXAMPLE:
     *  - TYPE_PERCENT: 10000 * 6% = 600
     *  - TYPE_FIXED: min(600, 10000) = 600
     *
     * @param numeric-string $priceCents
     *
     * @return numeric-string
     */
    private function calculateDiscount(string $priceCents, Coupon $coupon): string
    {
        return match ($coupon->getType()) {
            Coupon::TYPE_PERCENT => $this->calculatePercentDiscount($priceCents, (string) $coupon->getValue()),
            Coupon::TYPE_FIXED => $this->calculateFixedDiscount($priceCents, (string) $coupon->getValue()),
            default => '0',
        };
    }

    /**
     * Percent discount: priceCents * (percent / 100).
     *
     * EXAMPLE:
     *  - priceCents = "10000", percentValue = "6" -> 10000 * 0.06 = 600
     *
     * @param numeric-string $priceCents
     *
     * @return numeric-string
     */
    private function calculatePercentDiscount(string $priceCents, string $percentValue): string
    {
        $fraction = bcdiv($percentValue, '100', self::BC_SCALE);

        return bcmul($priceCents, $fraction, self::BC_SCALE);
    }

    /**
     * Fixed discount: min(fixedValue, priceCents).
     *
     * EXAMPLE:
     *  - priceCents = "10000", fixedValue = "600" -> 600
     *
     * @param numeric-string $priceCents
     * @param numeric-string $fixedValue
     *
     * @return numeric-string
     */
    private function calculateFixedDiscount(string $priceCents, string $fixedValue): string
    {
        return bccomp($fixedValue, $priceCents, self::BC_SCALE) > 0
            ? $priceCents
            : $fixedValue;
    }

    /**
     * Apply tax to the discounted price.
     *
     * EXAMPLE:
     *  - discountedPrice = "9400"
     *  - taxRate = 0.24
     *  - taxAmount = 9400 * 0.24 = 2256
     *  - taxedPrice = 9400 + 2256 = "11656"
     *
     * @param numeric-string $priceCents
     *
     * @return numeric-string
     */
    private function applyTax(string $priceCents, float $taxRate): string
    {
        $taxAmount = bcmul($priceCents, (string) $taxRate, self::BC_SCALE);

        return bcadd($priceCents, $taxAmount, self::BC_SCALE);
    }
}
