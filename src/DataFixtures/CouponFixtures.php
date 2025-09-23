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
            ['code' => 'P10', 'type' => 'percent', 'value' => 10.0],
            ['code' => 'P100', 'type' => 'percent', 'value' => 100.0],
            ['code' => 'CP6', 'type' => 'percent', 'value' => 6.0],
            ['code' => 'D15', 'type' => 'fixed', 'value' => 15.0],
            ['code' => 'D5', 'type' => 'fixed', 'value' => 5.0],
        ];

        foreach ($coupons as $couponData) {
            $coupon = new Coupon(
                $couponData['code'],
                $couponData['type'],
                $couponData['value']
            );

            $this->addReference('coupon_'.$couponData['code'], $coupon);


            $manager->persist($coupon);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [ProductFixtures::class];
    }
}
