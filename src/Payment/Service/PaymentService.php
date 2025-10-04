<?php

declare(strict_types=1);

namespace App\Payment\Service;

use App\Payment\Contract\PaymentProcessorInterface;
use App\Payment\Exception\PaymentException;
use App\ValueObject\Money;
use Symfony\Component\DependencyInjection\ServiceLocator;

final readonly class PaymentService implements PaymentServiceInterface
{
    /** @param ServiceLocator<PaymentProcessorInterface> $processors */
    public function __construct(private ServiceLocator $processors)
    {
    }

    #[\Override]
    public function pay(Money $money, string $processorAlias): void
    {
        if (!$this->processors->has($processorAlias)) {
            throw new PaymentException("Unknown payment processor $processorAlias.");
        }

        $paymentProcessor = $this->processors->get($processorAlias);
        $paymentProcessor->pay($money);
    }
}
