<?php

declare(strict_types=1);

namespace App\ValueObject;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class Money
{
    #[ORM\Column(name: "price_cents", type: "integer")]
    private int $cents;

    #[ORM\Column(name: "currency", type: "string", length: 3)]
    private string $currency;

    public function __construct(int $cents, string $currency = 'EUR')
    {
        $this->cents = $cents;
        $this->currency = $currency;
    }

    public function getCents(): int
    {
        return $this->cents;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }
}
