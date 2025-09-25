<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Coupon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @psalm-suppress PossiblyUnusedMethod
 *
 * @extends ServiceEntityRepository<Coupon>
 */
final class CouponRepository extends ServiceEntityRepository implements CouponRepositoryInterface
{
    /**
     * @psalm-suppress UnusedParam PossiblyUnusedMethod
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Coupon::class);
    }

    public function findActiveOrFail(string $code): Coupon
    {
        $coupon = $this->find($code);
        if (!$coupon instanceof Coupon || !$coupon->isActive()) {
            throw new \InvalidArgumentException('Invalid or inactive coupon');
        }

        return $coupon;
    }
}
