<?php

declare(strict_types=1);

namespace App\DTO;

use App\ValueObject\Money;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'CalculatePriceResponse',
    description: 'Response for calculating product price',
    type: 'object'
)]
final readonly class CalculatePriceResponse implements \JsonSerializable
{
    #[OA\Property(description: 'Total price in euros', type: 'number', format: 'float')]
    public float $price;

    #[OA\Property(description: 'Currency code', type: 'string', example: 'EUR')]
    public string $currency;

    public function __construct(Money $total)
    {
        $this->price = $total->getEuros();
        $this->currency = $total->getCurrency();
    }

    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function jsonSerialize(): array
    {
        return [
            'price' => $this->price,
            'currency' => $this->currency,
        ];
    }
}
