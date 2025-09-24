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
    #[ORM\Column(type: 'string', length: 64)]
    private string $id;

    #[ORM\Column(name: 'product_id', type: 'integer')]
    private int $productId;

    // Храним связь на купон — обеспечивает целостность и удобные JOIN'ы.
    #[ORM\ManyToOne(targetEntity: Coupon::class)]
    #[ORM\JoinColumn(name: 'coupon_id', referencedColumnName: 'code', nullable: true)]
    private ?Coupon $coupon = null;

    #[ORM\Column(name: 'tax_number', type: 'string', length: 64)]
    private string $taxNumber;

    #[ORM\Column(name: 'payment_processor', type: 'string', length: 32)]
    private string $paymentProcessor;

    // Храним исходную цену и итоговую сумму как Money embeddable.
    #[ORM\Embedded(class: Money::class, columnPrefix: 'price_')]
    private Money $price;

    #[ORM\Embedded(class: Money::class, columnPrefix: 'total_')]
    private Money $total;

    public function __construct(
        string $id,
        int $productId,
        Money $price,
        Money $total,
        string $taxNumber,
        string $paymentProcessor,
        ?Coupon $coupon = null
    ) {
        $this->id = $id;
        $this->productId = $productId;
        $this->price = $price;
        $this->total = $total;
        $this->taxNumber = $taxNumber;
        $this->paymentProcessor = $paymentProcessor;
        $this->coupon = $coupon;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function getTotal(): Money
    {
        return $this->total;
    }

    public function getCoupon(): ?Coupon
    {
        return $this->coupon;
    }
}
