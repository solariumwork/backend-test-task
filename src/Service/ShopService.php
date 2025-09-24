<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CalculatePriceRequest;
use App\DTO\CreateOrderDTO;
use App\DTO\PurchaseRequest;
use App\Entity\Order;
use App\Repository\ProductRepositoryInterface;
use App\Repository\CouponRepositoryInterface;
use App\Repository\OrderRepositoryInterface;
use App\ValueObject\Money;

final readonly class ShopService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private CouponRepositoryInterface $couponRepository,
        private PriceCalculatorService $calculator,
        private PaymentService $paymentService,
        private OrderRepositoryInterface $orderRepository
    ) {}

    public function calculatePrice(CalculatePriceRequest $dto): Money
    {
        $product = $this->productRepository->findOrFail($dto->product);
        $coupon = $dto->couponCode ? $this->couponRepository->findActiveOrFail($dto->couponCode) : null;

        return $this->calculator->calculate($product, $dto->taxNumber, $coupon);
    }

    public function purchase(PurchaseRequest $dto): Order
    {
        $product = $this->productRepository->findOrFail($dto->product);
        $coupon = $dto->couponCode ? $this->couponRepository->findActiveOrFail($dto->couponCode) : null;

        $total = $this->calculator->calculate($product, $dto->taxNumber, $coupon);
        $this->paymentService->pay($total, $dto->paymentProcessor);

        $orderDto = CreateOrderDTO::fromPurchaseRequest($dto, $product, $total, $coupon);

        return $this->orderRepository->create($orderDto);
    }
}
