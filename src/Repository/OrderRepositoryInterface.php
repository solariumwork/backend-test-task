<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\CreateOrderDto;
use App\Entity\Order;

interface OrderRepositoryInterface
{
    public function create(CreateOrderDto $dto): Order;

    public function save(Order $order): void;
}
