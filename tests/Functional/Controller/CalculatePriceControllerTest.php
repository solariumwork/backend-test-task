<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Kernel;
use App\Entity\Product;
use App\Entity\Coupon;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CalculatePriceControllerTest extends WebTestCase
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
        $container = $this->client->getContainer();
        $em = $container->get('doctrine')->getManager();

        $this->references['product_iphone'] = $em->getRepository(Product::class)
            ->findOneBy(['name' => 'Iphone']);

        $this->references['coupon_D15'] = $em->getRepository(Coupon::class)
            ->findOneBy(['code' => 'D15']);
    }

    public function testCalculatePriceSuccess(): void
    {
        $client = $this->request([
            'product' => $this->references['product_iphone']->getId(),
            'taxNumber' => 'DE123456789',
            'couponCode' => $this->references['coupon_D15']->getCode(),
        ]);

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('price', $data);
        $this->assertArrayHasKey('currency', $data);
        $this->assertEquals('EUR', $data['currency']);
        $this->assertGreaterThan(0, $data['price']);
    }

    public function testCalculatePriceInvalidTaxNumber(): void
    {
        $client = $this->request([
            'product' => $this->references['product_iphone']->getId(),
            'taxNumber' => 'INVALID',
            'couponCode' => $this->references['coupon_D15']->getCode(),
        ]);

        self::assertResponseStatusCodeSame(422);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Invalid tax number', $data['error']);
    }

    public function testCalculatePriceUnknownProduct(): void
    {
        $client = $this->request([
            'product' => 999,
            'taxNumber' => 'DE123456789',
        ]);

        self::assertResponseStatusCodeSame(422);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Product not found', $data['error']);
    }

    public function testCalculatePriceProductZero(): void
    {
        $client = $this->request([
            'product' => 0,
            'taxNumber' => 'DE123456789',
        ]);

        self::assertResponseStatusCodeSame(422);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
        $this->assertArrayHasKey('product', $data['errors']);
        $this->assertStringContainsString('positive', $data['errors']['product']);
    }

    public function testCalculatePriceEmptyCouponCode(): void
    {
        $client = $this->request([
            'product' => $this->references['product_iphone']->getId(),
            'taxNumber' => 'DE123456789',
            'couponCode' => '',
        ]);

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('price', $data);
    }

    public function testCalculatePriceMissingProduct(): void
    {
        $client = $this->request([
            'taxNumber' => 'DE123456789',
        ]);

        self::assertResponseStatusCodeSame(422);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $data);
        $this->assertArrayHasKey('product', $data['errors']);
        $this->assertStringContainsString('blank', $data['errors']['product']);
    }

    private function request(array $payload): KernelBrowser
    {
        $this->client->request(
            'POST',
            '/api/calculate-price',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        return $this->client;
    }

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }
}
