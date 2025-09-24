<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OrderRepository;
use App\ValueObject\Money;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
final class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: 'product_id', type: 'integer')]
    private int $productId;

    #[ORM\ManyToOne(targetEntity: Coupon::class)]
    #[ORM\JoinColumn(name: 'coupon_id', referencedColumnName: 'code', nullable: true)]
    private ?Coupon $coupon;

    #[ORM\Column(name: 'tax_number', type: 'string', length: 64)]
    private string $taxNumber;

    #[ORM\Column(name: 'payment_processor', type: 'string', length: 32)]
    private string $paymentProcessor;

    #[ORM\Embedded(class: Money::class, columnPrefix: 'price_')]
    private Money $originalPrice;

    #[ORM\Embedded(class: Money::class, columnPrefix: 'total_')]
    private Money $total;

    public function __construct(
        Money $originalPrice,
        Money $total,
        string $taxNumber,
        string $paymentProcessor,
        ?Coupon $coupon = null
    ) {
        $this->originalPrice = $originalPrice;
        $this->total = $total;
        $this->taxNumber = $taxNumber;
        $this->paymentProcessor = $paymentProcessor;
        $this->coupon = $coupon;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getTotal(): Money
    {
        return $this->total;
    }
}
