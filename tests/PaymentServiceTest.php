<?php

declare(strict_types=1);

namespace App\Tests;

use App\Service\PaymentService;
use App\ValueObject\Money;
use PHPUnit\Framework\TestCase;
use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor;
use Systemeio\TestForCandidates\PaymentProcessor\StripePaymentProcessor;

class PaymentServiceTest extends TestCase
{
    private PaymentService $paymentService;
    /** @var PaypalPaymentProcessor&\PHPUnit\Framework\MockObject\MockObject */
    private $paypalMock;

    /** @var StripePaymentProcessor&\PHPUnit\Framework\MockObject\MockObject */
    private $stripeMock;

    protected function setUp(): void
    {
        $this->paypalMock = $this->createMock(PaypalPaymentProcessor::class);
        $this->stripeMock = $this->createMock(StripePaymentProcessor::class);

        $this->paymentService = new PaymentService(
            $this->paypalMock,
            $this->stripeMock
        );
    }

    public function testPaypalPaymentWithPercentCoupon(): void
    {
        $originalPrice = 100.0;
        $discountPercent = 10.0;
        $expectedCents = (int)(($originalPrice * (1 - $discountPercent / 100)) * 100);

        $money = new Money($expectedCents);

        $this->paypalMock->expects($this->once())
            ->method('pay')
            ->with($expectedCents);

        $this->paymentService->pay($money, 'paypal');
    }

    public function testStripePaymentWithFixedCoupon(): void
    {
        $originalPrice = 50.0;
        $discountFixed = 5.0;
        $expectedCents = (int)(($originalPrice - $discountFixed) * 100);

        $money = new Money($expectedCents);

        $this->stripeMock->expects($this->once())
            ->method('processPayment')
            ->with($expectedCents);

        $this->paymentService->pay($money, 'stripe');
    }

    public function testPaymentWith100PercentDiscount(): void
    {
        $expectedCents = 0;

        $money = new Money($expectedCents);

        $this->stripeMock->expects($this->once())
            ->method('processPayment')
            ->with($expectedCents);

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
