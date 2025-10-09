<?php

declare(strict_types=1);

namespace App\Tax\Rate;

use App\Tax\Attribute\CountryTaxRate;
use App\Tax\Contract\TaxRateInterface;
use App\Tax\Enum\TaxRate;

/** @psalm-suppress UnusedClass */
#[CountryTaxRate(TaxRate::GERMANY)]
class GermanyTaxRate implements TaxRateInterface
{
    #[\Override]
    public function supports(string $taxNumber): bool
    {
        return 1 === preg_match('/^DE\d{9}$/i', $taxNumber);
    }

    #[\Override]
    public function get(): string
    {
        return TaxRate::GERMANY->value;
    }
}
