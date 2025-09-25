<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @psalm-suppress PossiblyUnusedMethod
 *
 * @extends ServiceEntityRepository<Product>
 */
final class ProductRepository extends ServiceEntityRepository implements ProductRepositoryInterface
{
    /**
     * @psalm-suppress UnusedParam PossiblyUnusedMethod
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findOrFail(int $id): Product
    {
        $product = $this->find($id);
        if (!$product instanceof Product) {
            throw new \InvalidArgumentException('Product not found');
        }

        return $product;
    }
}
