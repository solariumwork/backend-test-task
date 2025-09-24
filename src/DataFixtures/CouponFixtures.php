<?php

namespace App\DataFixtures;

use App\Entity\Coupon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CouponFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $coupons = [
            ['code' => 'P10', 'type' => 'percent', 'value' => 10],
            ['code' => 'P100', 'type' => 'percent', 'value' => 100],
            ['code' => 'CP6', 'type' => 'percent', 'value' => 6],
            ['code' => 'D15', 'type' => 'fixed', 'value' => 1500],
            ['code' => 'D5', 'type' => 'fixed', 'value' => 500],
        ];

        foreach ($coupons as $couponData) {
            $coupon = new Coupon(
                $couponData['code'],
                $couponData['type'],
                $couponData['value']
            );

            $manager->persist($coupon);
        }

        $manager->flush();
    }
}
