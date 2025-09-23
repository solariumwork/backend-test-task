<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CalculatePriceRequest;
use App\DTO\PurchaseRequest;
use App\Entity\Product;
use App\Entity\Coupon;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;

final class ShopService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PriceCalculatorService $calculator,
        private readonly PaymentService $paymentService
    ) {}

    public function calculatePrice(CalculatePriceRequest $dto): \App\ValueObject\Money
    {
        $product = $this->getProduct($dto->product);
        $coupon = $dto->couponCode ? $this->getCoupon($dto->couponCode) : null;

        return $this->calculator->calculate($product, $coupon, $dto->taxNumber);
    }

    public function purchase(PurchaseRequest $dto): Order
    {
        $product = $this->getProduct($dto->product);
        $coupon = $dto->couponCode ? $this->getCoupon($dto->couponCode) : null;

        $total = $this->calculator->calculate($product, $coupon, $dto->taxNumber);

        $this->paymentService->pay($total, $dto->paymentProcessor);

        $order = new Order(
            id: uniqid('order_', true),
            productId: $product->getId(),
            price: $product->getPrice(),
            total: $total,
            taxNumber: $dto->taxNumber,
            paymentProcessor: $dto->paymentProcessor,
            coupon: $coupon
        );

        $this->em->persist($order);
        $this->em->flush();

        return $order;
    }

    private function getProduct(int $id): Product
    {
        $product = $this->em->getRepository(Product::class)->find($id);
        if (!$product instanceof Product) {
            throw new \InvalidArgumentException('Product not found');
        }
        return $product;
    }

    private function getCoupon(string $code): ?Coupon
    {
        $coupon = $this->em->getRepository(Coupon::class)->find($code);
        if (!$coupon instanceof Coupon || !$coupon->isActive()) {
            throw new \InvalidArgumentException('Invalid or inactive coupon');
        }
        return $coupon;
    }
}
