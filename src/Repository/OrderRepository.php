<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\CreateOrderDto;
use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

final class OrderRepository extends ServiceEntityRepository implements OrderRepositoryInterface
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);

        $this->em = $this->getEntityManager();
    }

    public function create(CreateOrderDTO $dto): Order
    {
        $order = new Order(
            product: $dto->product,
            originalPrice: $dto->product->getPrice(),
            total: $dto->total,
            taxNumber: $dto->taxNumber,
            paymentProcessor: $dto->paymentProcessor,
            coupon: $dto->coupon
        );

        $this->em->persist($order);
        $this->em->flush();

        return $order;
    }
}
