<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CalculatePriceRequest;
use App\DTO\CreateOrderDto;
use App\DTO\PurchaseRequest;
use App\Entity\Order;
use App\Repository\CouponRepositoryInterface;
use App\Repository\OrderRepositoryInterface;
use App\Repository\ProductRepositoryInterface;
use App\ValueObject\Money;
use Psr\Log\LoggerInterface;

final readonly class ShopService implements ShopServiceInterface
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private CouponRepositoryInterface $couponRepository,
        private PriceCalculatorServiceInterface $calculator,
        private PaymentServiceInterface $paymentService,
        private OrderRepositoryInterface $orderRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function calculatePrice(CalculatePriceRequest $dto): Money
    {
        $product = $this->productRepository->findOrFail($dto->product);
        $coupon = $dto->couponCode ? $this->couponRepository->findActiveOrFail($dto->couponCode) : null;

        return $this->calculator->calculate($product, $dto->taxNumber, $coupon);
    }

    /**
     * @throws \Throwable
     */
    public function purchase(PurchaseRequest $dto): Order
    {
        $product = $this->productRepository->findOrFail($dto->product);
        $coupon = $dto->couponCode ? $this->couponRepository->findActiveOrFail($dto->couponCode) : null;

        $total = $this->calculator->calculate($product, $dto->taxNumber, $coupon);

        $orderDto = CreateOrderDto::fromPurchaseRequest($dto, $product, $total, $coupon);
        $order = $this->orderRepository->create($orderDto);

        $this->processPayment($order, $total, $dto->paymentProcessor);

        $this->orderRepository->save($order);

        return $order;
    }

    /**
     * @throws \Throwable
     */
    private function processPayment(Order $order, Money $amount, string $paymentProcessor): void
    {
        try {
            $this->paymentService->pay($amount, $paymentProcessor);
            $order->markAsPaid();
        } catch (\Throwable $e) {
            $order->markAsFailed();
            $this->logger->error(sprintf(
                'Payment failed for Order %d: %s',
                $order->getId(),
                $e->getMessage()
            ));
            throw $e;
        }
    }
}
