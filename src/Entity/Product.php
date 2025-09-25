<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProductRepository;
use App\ValueObject\Money;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'product')]
final class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private readonly string $name;

    #[ORM\Embedded(class: Money::class)]
    private readonly Money $price;

    public function __construct(string $name, Money $price)
    {
        $this->name = $name;
        $this->price = $price;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }
}
