<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $products = [
            ['name' => 'Iphone', 'price' => 100.0],
            ['name' => 'Наушники', 'price' => 20.0],
            ['name' => 'Чехол', 'price' => 10.0],
        ];

        foreach ($products as $i => $productData) {
            $product = new Product(
                $productData['name'],
                $productData['price']
            );

            $this->addReference('product_'.$i, $product);

            $manager->persist($product);
        }

        $manager->flush();
    }
}
