<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Payment\Contract\PaymentProcessorInterface;
use App\Payment\Exception\PaymentException;
use App\Payment\Service\PaymentService;
use App\ValueObject\Money;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;

/** @psalm-suppress UnusedClass */
class PaymentServiceTest extends TestCase
{
    private PaymentService $paymentService;

    private PaymentProcessorInterface&MockObject $paypalMock;
    private PaymentProcessorInterface&MockObject $stripeMock;

    #[\Override]
    protected function setUp(): void
    {
        $this->paypalMock = $this->createMock(PaymentProcessorInterface::class);
        $this->stripeMock = $this->createMock(PaymentProcessorInterface::class);

        $this->stripeMock->method('pay')->willReturnCallback(function (Money $money) {
            if (0 === $money->getCents()) {
                throw new PaymentException('Stripe payment failed');
            }
        });

        /** @var ServiceLocator<PaymentProcessorInterface> $locator */
        $locator = new ServiceLocator([
            'paypal' => fn () => $this->paypalMock,
            'stripe' => fn () => $this->stripeMock,
        ]);

        $this->paymentService = new PaymentService($locator);
    }

    public function testPaypalPayment(): void
    {
        $money = new Money(9000);

        $this->paypalMock->expects($this->once())
            ->method('pay')
            ->with($money);

        $this->paymentService->pay($money, 'paypal');
    }

    public function testStripePayment(): void
    {
        $money = new Money(45000);

        $this->stripeMock->expects($this->once())
            ->method('pay')
            ->with($money);

        $this->paymentService->pay($money, 'stripe');
    }

    public function testPaymentWith100PercentDiscount(): void
    {
        $money = new Money(0);

        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('Stripe payment failed');

        $this->paymentService->pay($money, 'stripe');
    }

    public function testUnknownProcessorThrowsException(): void
    {
        $money = new Money(1000);

        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('Unknown payment processor');

        $this->paymentService->pay($money, 'unknown');
    }
}
