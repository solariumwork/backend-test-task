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
     * Calculate the final price of a product, taking into account a coupon and tax.
     */
    public function calculatePrice(CalculatePriceRequest $dto): Money;

    /**
     * Perform a product purchase with payment through the selected processor.
     */
    public function purchase(PurchaseRequest $dto): Order;
}
