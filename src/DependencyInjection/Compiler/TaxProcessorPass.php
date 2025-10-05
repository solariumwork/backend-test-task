<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use App\Tax\Attribute\CountryTaxRate;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class TaxProcessorPass implements CompilerPassInterface
{
    #[\Override]
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $definition) {
            $class = $definition->getClass();
            if (!is_string($class) || !$this->isAppPaymentClass($class)) {
                continue;
            }

            /** @var class-string $class */
            $reflection = new \ReflectionClass($class);
            $attributes = $reflection->getAttributes(CountryTaxRate::class);
            if ($attributes) {
                $alias = $attributes[0]->newInstance()->taxRate->value;
                $definition->addTag('app.country_tax_rate', ['alias' => $alias]);
            }
        }
    }

    private function isAppPaymentClass(string $class): bool
    {
        return str_starts_with($class, 'App\\Tax\\Rate\\') && class_exists($class);
    }
}
