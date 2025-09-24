<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Coupon;

interface CouponRepositoryInterface
{
    public function findActiveOrFail(string $code): Coupon;
}
