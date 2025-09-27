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
     * @param CalculatePriceRequest $dto DTO containing product, taxNumber, and optional coupon code
     *
     * @return Money the total price including tax and discount
     *
     * @throws \Throwable if the product is not found or coupon is invalid
     */
    public function calculatePrice(CalculatePriceRequest $dto): Money;

    /**
     * Perform a product purchase with payment through the selected processor.
     *
     * @param PurchaseRequest $dto DTO containing product, taxNumber, optional coupon, and payment processor
     *
     * @return Order the created order
     *
     * @throws \Throwable if the product or coupon is invalid, or if payment fails
     */
    public function purchase(PurchaseRequest $dto): Order;
}
