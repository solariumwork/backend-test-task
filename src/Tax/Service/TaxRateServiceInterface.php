<?php

declare(strict_types=1);

namespace App\Tax\Service;

interface TaxRateServiceInterface
{
    /**
     * Returns the tax rate for the given VAT / tax number.
     *
     * The returned value must be a numeric string (e.g., "0.19" for 19%),
     * suitable for BCMath operations like bcmul, bcadd, etc.
     *
     * @param string $taxNumber the VAT / tax number to lookup
     *
     * @return numeric-string the applicable tax rate
     */
    public function getTaxRate(string $taxNumber): string;
}
