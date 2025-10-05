<?php

declare(strict_types=1);

namespace App\Tax\Service;

use App\Tax\Contract\TaxRateInterface;
use App\Tax\Exception\TaxRateException;
use Symfony\Component\DependencyInjection\ServiceLocator;

/** @psalm-suppress UnusedClass */
final readonly class TaxRateService implements TaxRateServiceInterface
{
    /** @param ServiceLocator<TaxRateInterface> $taxRates */
    public function __construct(private ServiceLocator $taxRates)
    {
    }

    #[\Override]
    public function getTaxRate(string $taxNumber): string
    {
        foreach ($this->taxRates->getProvidedServices() as $id => $_className) {
            $taxRate = $this->taxRates->get($id);
            if ($taxRate->supports($taxNumber)) {
                return $taxRate->get();
            }
        }

        throw new TaxRateException('Invalid tax number');
    }
}
