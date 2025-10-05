<?php

declare(strict_types=1);

namespace App\Tax\Contract;

/**
 * Interface TaxRateInterface.
 *
 * Represents a country-specific tax rate provider.
 *
 * Implementations should be able to:
 * 1. Determine if a given tax number is supported by this country/tax rate.
 * 2. Return the applicable tax rate as a string (numeric format, e.g. "0.19" for 19%).
 */
interface TaxRateInterface
{
    /**
     * Checks whether this tax rate supports the given tax number.
     *
     * @param string $taxNumber the VAT / tax number to check
     *
     * @return bool true if this tax rate can be applied to the given number, false otherwise
     */
    public function supports(string $taxNumber): bool;

    /**
     * Returns the tax rate as a numeric string (e.g., "0.19" for 19%).
     *
     * @return numeric-string
     */
    public function get(): string;
}
