<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Coupon;
use App\Entity\Product;
use App\Service\PriceCalculatorService;
use App\Tax\Exception\TaxRateException;
use App\Tax\Service\TaxRateServiceInterface;
use App\ValueObject\Money;
use PHPUnit\Framework\TestCase;

/** @psalm-suppress UnusedClass */
class PriceCalculatorServiceTest extends TestCase
{
    private PriceCalculatorService $calculator;

    #[\Override]
    protected function setUp(): void
    {
        $map = [
            'DE' => '0.19',
            'IT' => '0.22',
            'FR' => '0.20',
            'GR' => '0.24',
        ];

        $taxRateService = $this->createMock(TaxRateServiceInterface::class);
        $taxRateService
            ->method('getTaxRate')
            ->willReturnCallback(function (string $taxNumber) use ($map) {
                $prefix = strtoupper(substr($taxNumber, 0, 2));

                if (!isset($map[$prefix])) {
                    throw new TaxRateException('Invalid tax number');
                }

                return $map[$prefix]; // возвращаем numeric-string
            });

        $this->calculator = new PriceCalculatorService($taxRateService);
    }

    public function testCalculateWithoutCoupon(): void
    {
        $product = new Product('TestPhone', new Money(10000));

        $money = $this->calculator->calculateTotalAmount($product, 'DE123456789');

        // Germany 19% VAT: 10000 * 1.19 = 11900
        $this->assertEquals(11900, $money->getCents());
    }

    public function testCalculateWithPercentCoupon(): void
    {
        $product = new Product('TestPhone', new Money(10000));
        $coupon = new Coupon('P10', Coupon::TYPE_PERCENT, 10);

        $money = $this->calculator->calculateTotalAmount($product, 'IT12345678901', $coupon);

        // Italy 22% VAT: 10% discount → remaining 9000 * 1.22 = 10980
        $this->assertEquals(10980, $money->getCents());
    }

    public function testCalculateWithFixedCoupon(): void
    {
        $product = new Product('TestPhone', new Money(5000));
        $coupon = new Coupon('D5', Coupon::TYPE_FIXED, 500);

        $money = $this->calculator->calculateTotalAmount($product, 'FRAB123456789', $coupon);

        // France 20% VAT: 5000 - 500 = 4500 * 1.20 = 5400
        $this->assertEquals(5400, $money->getCents());
    }

    public function testFixedCouponGreaterThanPrice(): void
    {
        $product = new Product('CheapItem', new Money(300));
        $coupon = new Coupon('D500', Coupon::TYPE_FIXED, 500);

        $money = $this->calculator->calculateTotalAmount($product, 'GR123456789', $coupon);

        // Greece 24% VAT: price after coupon = 0 → tax 0 → total 0
        $this->assertEquals(0, $money->getCents());
    }

    public function testPercentCoupon100Percent(): void
    {
        $product = new Product('Expensive', new Money(10000));
        $coupon = new Coupon('P100', Coupon::TYPE_PERCENT, 100);

        $money = $this->calculator->calculateTotalAmount($product, 'GR123456789', $coupon);

        // Greece 24% VAT: remaining 0 → tax 0 → total 0
        $this->assertEquals(0, $money->getCents());
    }

    public function testPercentCouponAlmostFull(): void
    {
        $product = new Product('ExpensiveItem', new Money(10000));
        $coupon = new Coupon('P99', Coupon::TYPE_PERCENT, 99);

        $money = $this->calculator->calculateTotalAmount($product, 'GR123456789', $coupon);

        // Greece 24% VAT: 1% remaining from 10000 → 100 * 1.24 = 124
        $this->assertEquals(124, $money->getCents());
    }

    public function testInvalidTaxNumberThrowsException(): void
    {
        $this->expectException(TaxRateException::class);
        $this->expectExceptionMessage('Invalid tax number');

        $product = new Product('TestPhone', new Money(10000));
        $this->calculator->calculateTotalAmount($product, 'INVALID123');
    }
}
