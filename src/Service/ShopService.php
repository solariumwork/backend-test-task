<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CalculatePriceRequest;
use App\DTO\CreateOrderDto;
use App\DTO\PurchaseRequest;
use App\Entity\Coupon;
use App\Entity\Order;
use App\Entity\Product;
use App\Enum\PaymentStatus;
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

    #[\Override]
    public function calculatePrice(CalculatePriceRequest $dto): Money
    {
        [$product, $coupon] = $this->retrieveProductAndCoupon($dto->product, $dto->couponCode);
        return $this->calculator->calculateTotalAmount($product, $dto->taxNumber, $coupon);
    }

    #[\Override]
    public function purchase(PurchaseRequest $dto): Order
    {
        [$product, $coupon] = $this->retrieveProductAndCoupon($dto->product, $dto->couponCode);

        $total = $this->calculator->calculateTotalAmount($product, $dto->taxNumber, $coupon);

        $order = $this->createPendingOrder($dto, $product, $total, $coupon);

        $this->handlePayment($order, $total, $dto->paymentProcessor);

        return $order;
    }

    private function createPendingOrder(PurchaseRequest $dto, Product $product, Money $total, ?Coupon $coupon): Order
    {
        $orderDto = CreateOrderDto::fromPurchaseRequest($dto, $product, $total, $coupon);
        return $this->orderRepository->create($orderDto);
    }

    private function handlePayment(Order $order, Money $amount, string $processor): void
    {
        try {
            $this->paymentService->pay($amount, $processor);
            $status = PaymentStatus::PAID;
        } catch (\Throwable $e) {
            $status = PaymentStatus::FAILED;

            $payload = [
                'orderId' => $order->getId(),
                'amount' => $amount->getEuros(),
                'currency' => $amount->getCurrency(),
                'processor' => $processor,
                'exception' => $e,
            ];
            $this->logger->error('Payment failed', $payload);
            throw $e;
        } finally {
            if ($status !== null) {
                $this->updatePaymentStatus($order, $status);
            }
        }
    }

    /**
     * @return array{0: Product, 1: ?Coupon}
     */
    private function retrieveProductAndCoupon(int $productId, ?string $couponCode): array
    {
        $product = $this->productRepository->findOrFail($productId);
        $coupon = $couponCode ? $this->couponRepository->findActiveOrFail($couponCode) : null;
        return [$product, $coupon];
    }

    private function updatePaymentStatus(Order $order, PaymentStatus $status): void
    {
        match ($status) {
            PaymentStatus::PAID => $order->markAsPaid(),
            PaymentStatus::FAILED => $order->markAsFailed(),
            default => null
        };

        $this->orderRepository->save($order);
    }
}
