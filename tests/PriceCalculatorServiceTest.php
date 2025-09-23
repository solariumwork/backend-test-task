<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Service\PriceCalculatorService;
use App\Entity\Product;
use App\Entity\Coupon;
use App\ValueObject\Money;

class PriceCalculatorServiceTest extends TestCase
{
    private PriceCalculatorService $calculator;

    protected function setUp(): void
    {
        $this->calculator = new PriceCalculatorService();
    }

    public function testCalculateWithoutCoupon(): void
    {
        $product = new Product('TestPhone', new Money(10000));

        $money = $this->calculator->calculate($product, 'DE123456789');

        $expectedCents = (int) round(10000 * 1.19);
        $this->assertEquals($expectedCents, $money->getCents());
    }

    public function testCalculateWithPercentCoupon(): void
    {
        $product = new Product('TestPhone', new Money(10000));
        $coupon = new Coupon('P10', Coupon::TYPE_PERCENT, 10);

        $money = $this->calculator->calculate($product, 'IT12345678901', $coupon);

        $totalAfterDiscount = 10000 - (int) round(10000 * 0.10);
        $expectedCents = (int) round($totalAfterDiscount * 1.22);
        $this->assertEquals($expectedCents, $money->getCents());
    }

    public function testCalculateWithFixedCoupon(): void
    {
        $product = new Product('TestPhone', new Money(5000));
        $coupon = new Coupon('D5', Coupon::TYPE_FIXED, 500);

        $money = $this->calculator->calculate($product, 'FRAB123456789', $coupon);

        $totalAfterDiscount = 5000 - 500;
        $expectedCents = (int) round($totalAfterDiscount * 1.20);
        $this->assertEquals($expectedCents, $money->getCents());
    }

    public function testFixedCouponGreaterThanPrice(): void
    {
        $product = new Product('CheapItem', new Money(300));
        $coupon = new Coupon('D500', Coupon::TYPE_FIXED, 5000);

        $money = $this->calculator->calculate($product, 'GR123456789', $coupon);

        $expectedCents = 0;
        $this->assertEquals($expectedCents, $money->getCents());
    }

    public function testPercentCoupon100Percent(): void
    {
        $product = new Product('Expensive', new Money(10000));
        $coupon = new Coupon('P100', Coupon::TYPE_PERCENT, 100);

        $money = $this->calculator->calculate($product, 'GR123456789', $coupon);

        $expectedCents = 0;
        $this->assertEquals($expectedCents, $money->getCents());
    }

    public function testInvalidTaxNumberThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid tax number');

        $product = new Product('TestPhone', new Money(10000));
        $this->calculator->calculate($product, 'INVALID123');
    }
}
