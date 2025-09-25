<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\Coupon;
use App\Entity\Product;
use App\Kernel;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PurchaseControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private array $references = [];

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->loadReferences();
    }

    private function loadReferences(): void
    {
        $em = $this->client->getContainer()->get('doctrine')->getManager();

        $this->references['product_iphone'] = $em->getRepository(Product::class)
            ->findOneBy(['name' => 'Iphone']);
        $this->references['coupon_D15'] = $em->getRepository(Coupon::class)
            ->findOneBy(['code' => 'D15']);
    }

    public function testPurchaseSuccess(): void
    {
        $payload = [
            'product' => $this->references['product_iphone']->getId(),
            'taxNumber' => 'DE123456789',
            'couponCode' => $this->references['coupon_D15']->getCode(),
            'paymentProcessor' => 'paypal',
        ];

        $this->client->request(
            'POST',
            '/api/purchase',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        self::assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('orderId', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('currency', $data);

        $this->assertEquals('EUR', $data['currency']);
        $this->assertGreaterThan(0, $data['total']);
    }

    public function testPurchaseInvalidTaxNumber(): void
    {
        $payload = [
            'product' => $this->references['product_iphone']->getId(),
            'taxNumber' => 'INVALID',
            'paymentProcessor' => 'paypal',
        ];

        $this->client->request(
            'POST',
            '/api/purchase',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        self::assertResponseStatusCodeSame(422);

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);
        $this->assertEquals('Invalid tax number', $data['errors'][0]);
    }

    public function testPurchaseUnknownProduct(): void
    {
        $payload = [
            'product' => 999,
            'taxNumber' => 'DE123456789',
            'paymentProcessor' => 'paypal',
        ];

        $this->client->request(
            'POST',
            '/api/purchase',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        self::assertResponseStatusCodeSame(422);

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);
        $this->assertEquals('Product not found', $data['errors'][0]);
    }

    public function testPurchaseInvalidCoupon(): void
    {
        $payload = [
            'product' => $this->references['product_iphone']->getId(),
            'taxNumber' => 'DE123456789',
            'couponCode' => 'INVALID',
            'paymentProcessor' => 'paypal',
        ];

        $this->client->request(
            'POST',
            '/api/purchase',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        self::assertResponseStatusCodeSame(422);

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);
        $this->assertEquals('Invalid or inactive coupon', $data['errors'][0]);
    }

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }
}
