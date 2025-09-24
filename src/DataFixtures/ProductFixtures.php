<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\ValueObject\Money;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $products = [
            ['name' => 'Iphone', 'price' => 100],
            ['name' => 'Наушники', 'price' => 20],
            ['name' => 'Чехол', 'price' => 10],
        ];

        foreach ($products as $productData) {
            $product = new Product(
                $productData['name'],
                new Money(($productData['price'] * 100))
            );

            $manager->persist($product);
        }

        $manager->flush();
    }
}
