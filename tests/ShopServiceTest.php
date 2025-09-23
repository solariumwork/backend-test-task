<?php

declare(strict_types=1);

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\ShopService;
use App\Service\PriceCalculatorService;
use App\Service\PaymentService;
use App\Entity\Product;
use App\Entity\Coupon;
use App\Entity\Order;
use App\ValueObject\Money;
use App\DTO\CalculatePriceRequest;
use App\DTO\PurchaseRequest;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ShopServiceTest extends TestCase
{
    private ShopService $shopService;
    private EntityManagerInterface $emMock;
    private PriceCalculatorService $calculatorMock;
    private PaymentService $paymentMock;

    protected function setUp(): void
    {
        $this->emMock = $this->createMock(EntityManagerInterface::class);
        $this->calculatorMock = $this->createMock(PriceCalculatorService::class);
        $this->paymentMock = $this->createMock(PaymentService::class);

        $this->shopService = new ShopService(
            $this->emMock,
            $this->calculatorMock,
            $this->paymentMock
        );
    }

    public function testCalculatePriceWithoutCoupon(): void
    {
        $product = new Product('Test', new Money(1000));
        $product->setId(1);

        $productRepoMock = $this->createMock(EntityRepository::class);
        $productRepoMock->method('find')->with(1)->willReturn($product);

        $this->emMock->method('getRepository')
            ->with(Product::class)
            ->willReturn($productRepoMock);

        $this->calculatorMock->method('calculate')
            ->with($product, 'DE123456789', null)
            ->willReturn(new Money(1190));

        $dto = new CalculatePriceRequest(1, 'DE123456789', null);

        $result = $this->shopService->calculatePrice($dto);

        $this->assertInstanceOf(Money::class, $result);
        $this->assertEquals(1190, $result->getCents());
    }

    public function testCalculatePriceWithCoupon(): void
    {
        $product = new Product('Test', new Money(1000));
        $product->setId(1);

        $coupon = $this->createMock(Coupon::class);
        $coupon->method('isActive')->willReturn(true);

        $productRepoMock = $this->createMock(EntityRepository::class);
        $productRepoMock->method('find')->willReturn($product);

        $couponRepoMock = $this->createMock(EntityRepository::class);
        $couponRepoMock->method('find')->willReturn($coupon);

        $this->emMock->method('getRepository')
            ->willReturnMap([
                [Product::class, $productRepoMock],
                [Coupon::class, $couponRepoMock],
            ]);

        $this->calculatorMock->method('calculate')
            ->with($product, 'DE123456789', $coupon)
            ->willReturn(new Money(1000));

        $dto = new CalculatePriceRequest(1, 'DE123456789', 'COUPON10');

        $result = $this->shopService->calculatePrice($dto);

        $this->assertInstanceOf(Money::class, $result);
        $this->assertEquals(1000, $result->getCents());
    }

    public function testPurchase(): void
    {
        $product = new Product('Test', new Money(1000));
        $product->setId(1);

        $coupon = $this->createMock(Coupon::class);
        $coupon->method('isActive')->willReturn(true);

        $productRepoMock = $this->createMock(EntityRepository::class);
        $productRepoMock->method('find')->willReturn($product);

        $couponRepoMock = $this->createMock(EntityRepository::class);
        $couponRepoMock->method('find')->willReturn($coupon);

        $this->emMock->method('getRepository')
            ->willReturnMap([
                [Product::class, $productRepoMock],
                [Coupon::class, $couponRepoMock],
            ]);

        $totalMoney = new Money(1190);

        $this->calculatorMock->method('calculate')
            ->with($product, 'DE123456789', $coupon)
            ->willReturn($totalMoney);

        $this->paymentMock->expects($this->once())
            ->method('pay')
            ->with($totalMoney, 'paypal');

        $this->emMock->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Order::class));

        $this->emMock->expects($this->once())
            ->method('flush');

        $dto = new PurchaseRequest(1, 'DE123456789', 'COUPON10', 'paypal');

        $order = $this->shopService->purchase($dto);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($product->getId(), $order->getProductId());
        $this->assertEquals($totalMoney, $order->getTotal());
        $this->assertEquals('DE123456789', $order->getTaxNumber());
        $this->assertEquals('paypal', $order->getPaymentProcessor());
    }

    public function testProductNotFoundThrowsException(): void
    {
        $productRepoMock = $this->createMock(EntityRepository::class);
        $productRepoMock->method('find')->willReturn(null);

        $this->emMock->method('getRepository')
            ->with(Product::class)
            ->willReturn($productRepoMock);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product not found');

        $dto = new CalculatePriceRequest(999, 'DE123456789', null);

        $this->shopService->calculatePrice($dto);
    }

    public function testCouponInactiveThrowsException(): void
    {
        $product = new Product('Test', new Money(1000));
        $product->setId(1);

        $coupon = $this->createMock(Coupon::class);
        $coupon->method('isActive')->willReturn(false);

        $productRepoMock = $this->createMock(EntityRepository::class);
        $productRepoMock->method('find')->willReturn($product);

        $couponRepoMock = $this->createMock(EntityRepository::class);
        $couponRepoMock->method('find')->willReturn($coupon);

        $this->emMock->method('getRepository')
            ->willReturnMap([
                [Product::class, $productRepoMock],
                [Coupon::class, $couponRepoMock],
            ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid or inactive coupon');

        $dto = new CalculatePriceRequest(1, 'DE123456789', 'COUPON10');

        $this->shopService->calculatePrice($dto);
    }
}
