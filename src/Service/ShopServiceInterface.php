<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CalculatePriceRequest;
use App\DTO\PurchaseRequest;
use App\Entity\Order;
use App\ValueObject\Money;

interface ShopServiceInterface
{
    /**
     * Calculate the final price of a product, taking into account tax and optional coupon.
     *
     * @param CalculatePriceRequest $dto DTO containing product, taxNumber, and optional coupon code.
     *
     * @return Money The total price including tax and discount.
     *
     * @throws \Throwable If the product is not found or coupon is invalid.
     */
    public function calculatePrice(CalculatePriceRequest $dto): Money;

    /**
     * Perform a product purchase with payment through the selected processor.
     *
     * @param PurchaseRequest $dto DTO containing product, taxNumber, optional coupon, and payment processor.
     *
     * @return Order The created order.
     *
     * @throws \Throwable If the product or coupon is invalid, or if payment fails.
     */
    public function purchase(PurchaseRequest $dto): Order;
}
