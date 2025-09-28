<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\PaymentStatus;
use App\Repository\OrderRepository;
use App\ValueObject\Money;
use Doctrine\ORM\Mapping as ORM;

/** @psalm-suppress UnusedProperty */
#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
final class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false)]
    private Product $product;

    #[ORM\ManyToOne(targetEntity: Coupon::class)]
    #[ORM\JoinColumn(name: 'coupon_id', referencedColumnName: 'code', nullable: true)]
    private ?Coupon $coupon;

    #[ORM\Column(name: 'tax_number', type: 'string', length: 30)]
    private string $taxNumber;

    #[ORM\Column(name: 'payment_processor', type: 'string', length: 30)]
    private string $paymentProcessor;

    #[ORM\Column(type: 'string', length: 30)]
    private string $paymentStatus;

    #[ORM\Embedded(class: Money::class, columnPrefix: 'price_')]
    private Money $originalPrice;

    #[ORM\Embedded(class: Money::class, columnPrefix: 'total_')]
    private Money $total;

    public function __construct(
        Product $product,
        Money $originalPrice,
        Money $total,
        string $taxNumber,
        string $paymentProcessor,
        ?Coupon $coupon = null,
    ) {
        $this->product = $product;
        $this->originalPrice = $originalPrice;
        $this->total = $total;
        $this->taxNumber = $taxNumber;
        $this->paymentProcessor = $paymentProcessor;
        $this->coupon = $coupon;

        $this->paymentStatus = PaymentStatus::PENDING->value;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTotal(): Money
    {
        return $this->total;
    }

    public function markAsPaid(): void
    {
        $this->paymentStatus = PaymentStatus::PAID->value;
    }

    public function markAsFailed(): void
    {
        $this->paymentStatus = PaymentStatus::FAILED->value;
    }
}
