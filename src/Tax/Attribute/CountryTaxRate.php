<?php

declare(strict_types=1);

namespace App\Tax\Attribute;

use App\Tax\Enum\TaxRate;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class CountryTaxRate
{
    public function __construct(public TaxRate $taxRate)
    {
    }
}
