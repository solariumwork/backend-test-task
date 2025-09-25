<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\ValueObject\Money;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $products = [
            ['name' => 'Iphone', 'price' => 100],
            ['name' => 'Наушники', 'price' => 20],
            ['name' => 'Чехол', 'price' => 10],
        ];

        foreach ($products as $index => $productData) {
            $product = new Product(
                $productData['name'],
                new Money($productData['price'] * 100)
            );

            $manager->persist($product);

            $this->addReference('product_'.$index, $product);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['default', 'test'];
    }
}
