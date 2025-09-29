<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\PaymentService;
use App\ValueObject\Money;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

/** @psalm-suppress UnusedClass */
class PaymentServiceTest extends TestCase
{
    private PaymentService $paymentService;
    /** @var PaypalPaymentProcessor&MockObject */
    private $paypalMock;

    /** @var StripePaymentProcessor&MockObject */
    private $stripeMock;

    #[\Override]
    protected function setUp(): void
    {
        $this->paypalMock = $this->createMock(PaypalPaymentProcessor::class);
        $this->stripeMock = $this->createMock(StripePaymentProcessor::class);

        $this->paymentService = new PaymentService(
            $this->paypalMock,
            $this->stripeMock
        );
    }

    public function testPaypalPayment(): void
    {
        $expectedCents = 9000;
        $money = new Money($expectedCents);

        $this->paypalMock->expects($this->once())
            ->method('pay')
            ->with($expectedCents);

        $this->paymentService->pay($money, 'paypal');
    }

    public function testStripePayment(): void
    {
        $expectedCents = 45000;
        $money = new Money($expectedCents);

        $this->stripeMock->expects($this->once())
            ->method('processPayment')
            ->with($money->getEuros())
            ->willReturn(true);

        $this->paymentService->pay($money, 'stripe');
    }

    public function testPaymentWith100PercentDiscount(): void
    {
        $expectedCents = 0;
        $money = new Money($expectedCents);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stripe payment failed');

        $this->paymentService->pay($money, 'stripe');
    }

    public function testUnknownProcessorThrowsException(): void
    {
        $money = new Money(1000);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown payment processor');

        $this->paymentService->pay($money, 'unknown');
    }
}
