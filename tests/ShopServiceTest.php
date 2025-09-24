<?php

declare(strict_types=1);

namespace App\Tests;

use App\DTO\CalculatePriceRequest;
use App\DTO\PurchaseRequest;
use App\Entity\Product;
use App\Entity\Order;
use App\Repository\CouponRepositoryInterface;
use App\Repository\OrderRepositoryInterface;
use App\Repository\ProductRepository;
use App\Repository\CouponRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepositoryInterface;
use App\Service\PaymentService;
use App\Service\PriceCalculatorService;
use App\Service\ShopService;
use App\ValueObject\Money;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ShopServiceTest extends TestCase
{
    /** @var ProductRepository&MockObject */
    private $productRepository;

    /** @var CouponRepository&MockObject */
    private $couponRepository;

    /** @var OrderRepository&MockObject */
    private $orderRepository;

    /** @var PriceCalculatorService&MockObject */
    private $calculator;

    /** @var PaymentService&MockObject */
    private $paymentService;

    private ShopService $shopService;

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->couponRepository = $this->createMock(CouponRepositoryInterface::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->calculator = $this->createMock(PriceCalculatorService::class);
        $this->paymentService = $this->createMock(PaymentService::class);

        $this->shopService = new ShopService(
            $this->productRepository,
            $this->couponRepository,
            $this->calculator,
            $this->paymentService,
            $this->orderRepository
        );
    }

    public function testCalculatePrice(): void
    {
        $dto = new CalculatePriceRequest(product: 1, taxNumber: 'DE123456789', couponCode: 'D10');
        $product = new Product();
        $money = new Money(12000);

        $this->productRepository->method('findOrFail')->with(1)->willReturn($product);
        $this->couponRepository->method('findActiveOrFail')->with('D10')->willReturn(null);
        $this->calculator->method('calculate')->with($product, 'DE123456789', null)->willReturn($money);

        $result = $this->shopService->calculatePrice($dto);

        $this->assertSame($money, $result);
    }

    public function testPurchase(): void
    {
        $dto = new PurchaseRequest(product: 1, taxNumber: 'DE123456789', couponCode: null, paymentProcessor: 'paypal');
        $product = new Product();
        $money = new Money(12000);
        $order = $this->createMock(Order::class);

        $this->productRepository->method('findOrFail')->willReturn($product);
        $this->couponRepository->method('findActiveOrFail')->willReturn(null);
        $this->calculator->method('calculate')->willReturn($money);
        $this->paymentService->expects($this->once())->method('pay')->with($money, 'paypal');
        $this->orderRepository->method('create')->willReturn($order);

        $result = $this->shopService->purchase($dto);

        $this->assertSame($order, $result);
    }
}
