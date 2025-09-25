<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\DTO\CalculatePriceRequest;
use App\DTO\PurchaseRequest;
use App\Entity\Coupon;
use App\Entity\Order;
use App\Entity\Product;
use App\Repository\CouponRepositoryInterface;
use App\Repository\OrderRepositoryInterface;
use App\Repository\ProductRepositoryInterface;
use App\Service\PaymentService;
use App\Service\PriceCalculatorServiceInterface;
use App\Service\ShopService;
use App\ValueObject\Money;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class ShopServiceTest extends TestCase
{
    private ShopService $shopService;

    /** @var ProductRepositoryInterface&MockObject */
    private $productRepo;

    /** @var CouponRepositoryInterface&MockObject */
    private $couponRepo;

    /** @var PriceCalculatorServiceInterface&MockObject */
    private $calculator;

    /** @var PaymentService&MockObject */
    private $paymentService;

    /** @var OrderRepositoryInterface&MockObject */
    private $orderRepo;

    protected function setUp(): void
    {
        $this->productRepo = $this->createMock(ProductRepositoryInterface::class);
        $this->couponRepo = $this->createMock(CouponRepositoryInterface::class);
        $this->calculator = $this->createMock(PriceCalculatorServiceInterface::class);
        $this->paymentService = $this->createMock(PaymentService::class);
        $this->orderRepo = $this->createMock(OrderRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->shopService = new ShopService(
            $this->productRepo,
            $this->couponRepo,
            $this->calculator,
            $this->paymentService,
            $this->orderRepo,
            $this->logger
        );
    }

    public function testCalculatePriceWithoutCoupon(): void
    {
        $product = new Product('Iphone', new Money(10000));

        $dto = new CalculatePriceRequest();
        $dto->product = 1;
        $dto->taxNumber = 'DE123456789';
        $dto->couponCode = null;

        $money = new Money(11900); // 10000 + 19% tax

        $this->productRepo->expects($this->once())
            ->method('findOrFail')
            ->with(1)
            ->willReturn($product);

        $this->calculator->expects($this->once())
            ->method('calculate')
            ->with($product, 'DE123456789', null)
            ->willReturn($money);

        $result = $this->shopService->calculatePrice($dto);
        $this->assertSame($money, $result);
    }

    public function testCalculatePriceWithCoupon(): void
    {
        $product = new Product('Iphone', new Money(10000));
        $coupon = new Coupon('D10', Coupon::TYPE_PERCENT, 10);

        $dto = new CalculatePriceRequest();
        $dto->product = 1;
        $dto->taxNumber = 'DE123456789';
        $dto->couponCode = 'D10';

        $money = new Money(10710); // 10000 - 10% + 19% tax

        $this->productRepo->expects($this->once())
            ->method('findOrFail')
            ->with(1)
            ->willReturn($product);

        $this->couponRepo->expects($this->once())
            ->method('findActiveOrFail')
            ->with('D10')
            ->willReturn($coupon);

        $this->calculator->expects($this->once())
            ->method('calculate')
            ->with($product, 'DE123456789', $coupon)
            ->willReturn($money);

        $result = $this->shopService->calculatePrice($dto);
        $this->assertSame($money, $result);
    }

    public function testPurchase(): void
    {
        $product = new Product('Iphone', new Money(10000));
        $coupon = new Coupon('D10', Coupon::TYPE_PERCENT, 10);
        $order = new Order($product, new Money(10000), new Money(10710), 'DE123456789', 'paypal', $coupon);

        $dto = new PurchaseRequest();
        $dto->product = 1;
        $dto->taxNumber = 'DE123456789';
        $dto->couponCode = 'D10';
        $dto->paymentProcessor = 'paypal';

        $money = new Money(10710);

        $this->productRepo->expects($this->once())
            ->method('findOrFail')
            ->with(1)
            ->willReturn($product);

        $this->couponRepo->expects($this->once())
            ->method('findActiveOrFail')
            ->with('D10')
            ->willReturn($coupon);

        $this->calculator->expects($this->once())
            ->method('calculate')
            ->with($product, 'DE123456789', $coupon)
            ->willReturn($money);

        $this->paymentService->expects($this->once())
            ->method('pay')
            ->with($money, 'paypal');

        $this->orderRepo->expects($this->once())
            ->method('create')
            ->willReturn($order);

        $result = $this->shopService->purchase($dto);
        $this->assertSame($order, $result);
    }

    public function testPurchaseLogsErrorOnPaymentFailure(): void
    {
        $product = new Product('Iphone', new Money(10000));
        $coupon = null;
        $order = new Order($product, new Money(10000), new Money(11900), 'DE123456789', 'paypal', $coupon);

        $dto = new PurchaseRequest();
        $dto->product = 1;
        $dto->taxNumber = 'DE123456789';
        $dto->couponCode = null;
        $dto->paymentProcessor = 'paypal';

        $money = new Money(11900);

        $this->productRepo->method('findOrFail')->willReturn($product);
        $this->calculator->method('calculate')->willReturn($money);
        $this->orderRepo->method('create')->willReturn($order);

        $this->paymentService->method('pay')
            ->willThrowException(new \RuntimeException('Payment gateway error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Payment failed'));

        $this->expectException(\RuntimeException::class);

        $this->shopService->purchase($dto);
    }
}
