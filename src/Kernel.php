<?php

namespace App;

use App\DependencyInjection\Compiler\PaymentProcessorPass;
use App\DependencyInjection\Compiler\TaxProcessorPass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    #[\Override]
    protected function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new PaymentProcessorPass());
        $container->addCompilerPass(new TaxProcessorPass());
    }
}
