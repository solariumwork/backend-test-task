<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\CreateOrderDTO;
use App\Entity\Order;

interface OrderRepositoryInterface
{
    public function create(CreateOrderDTO $dto): Order;

    public function save(Order $order): void;
}
