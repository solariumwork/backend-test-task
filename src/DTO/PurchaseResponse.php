<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Order;
use App\ValueObject\Money;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'PurchaseResponse',
    description: 'Response for a purchase',
    type: 'object'
)]
final readonly class PurchaseResponse implements \JsonSerializable
{
    #[OA\Property(description: 'Unique order ID', type: 'integer', example: 101)]
    public int $orderId;

    #[OA\Property(description: 'Total amount in euros', type: 'number', format: 'float')]
    public float $total;

    #[OA\Property(description: 'Currency code', type: 'string', example: 'EUR')]
    public string $currency;

    public function __construct(int $orderId, Money $total)
    {
        $this->orderId = $orderId;
        $this->total = $total->getEuros();
        $this->currency = $total->getCurrency();
    }

    public static function fromOrder(Order $order): self
    {
        return new self(
            orderId: (int) $order->getId(),
            total: $order->getTotal()
        );
    }

    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function jsonSerialize(): array
    {
        return [
            'orderId' => $this->orderId,
            'total' => $this->total,
            'currency' => $this->currency,
        ];
    }
}
