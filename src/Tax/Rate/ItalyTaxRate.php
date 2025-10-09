<?php

declare(strict_types=1);

namespace App\Tax\Rate;

use App\Tax\Attribute\CountryTaxRate;
use App\Tax\Contract\TaxRateInterface;
use App\Tax\Enum\TaxRate;

/** @psalm-suppress UnusedClass */
#[CountryTaxRate(TaxRate::ITALY)]
class ItalyTaxRate implements TaxRateInterface
{
    #[\Override]
    public function supports(string $taxNumber): bool
    {
        return 1 === preg_match('/^IT\d{11}$/i', $taxNumber);
    }

    #[\Override]
    public function get(): string
    {
        return TaxRate::ITALY->value;
    }
}
