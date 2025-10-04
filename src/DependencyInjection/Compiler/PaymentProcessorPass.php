<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use App\Payment\Attribute\PaymentProcessor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class PaymentProcessorPass implements CompilerPassInterface
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
            $attributes = $reflection->getAttributes(PaymentProcessor::class);
            if ($attributes) {
                $alias = $attributes[0]->newInstance()->type->value;
                $definition->addTag('app.payment_processor', ['alias' => $alias]);
            }
        }
    }

    private function isAppPaymentClass(string $class): bool
    {
        return str_starts_with($class, 'App\\Payment\\') && class_exists($class);
    }
}
