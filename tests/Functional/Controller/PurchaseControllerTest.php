<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\Coupon;
use App\Entity\Product;
use App\Kernel;
use App\Tests\Functional\FunctionalTestCase;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/** @psalm-suppress UnusedClass */
class PurchaseControllerTest extends FunctionalTestCase
{
    private KernelBrowser $client;

    /** @var array<string, Product|Coupon|null> */
    private array $references = [];

    #[\Override]
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->loadReferences();
    }

    private function loadReferences(): void
    {
        $container = $this->client->getContainer();

        /** @var ManagerRegistry $doctrine */
        $doctrine = $container->get('doctrine');
        $em = $doctrine->getManager();

        $this->references['product_iphone'] = $em->getRepository(Product::class)
            ->findOneBy(['name' => 'Iphone']);
        $this->references['coupon_D15'] = $em->getRepository(Coupon::class)
            ->findOneBy(['code' => 'D15']);
    }

    public function testPurchaseSuccess(): void
    {
        /** @var Product $product */
        $product = $this->references['product_iphone'];
        /** @var Coupon $coupon */
        $coupon = $this->references['coupon_D15'];

        $payload = [
            'product' => $product->getId(),
            'taxNumber' => 'DE123456789',
            'couponCode' => $coupon->getCode(),
            'paymentProcessor' => 'paypal',
        ];

        $this->request($payload);

        self::assertResponseIsSuccessful();

        $data = $this->decodeResponse();
        $this->assertArrayHasKey('orderId', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('currency', $data);

        $this->assertEquals('EUR', $data['currency']);
        $this->assertGreaterThan(0, $data['total']);
    }

    public function testPurchaseInvalidTaxNumber(): void
    {
        /** @var Product $product */
        $product = $this->references['product_iphone'];

        $payload = [
            'product' => $product->getId(),
            'taxNumber' => 'INVALID',
            'paymentProcessor' => 'paypal',
        ];

        $this->request($payload);

        self::assertResponseStatusCodeSame(422);

        $data = $this->decodeResponse();
        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);

        $this->assertArrayHasKey('taxNumber', $data['errors']);
        $this->assertStringContainsString('Invalid tax number', (string) $data['errors']['taxNumber']);
    }

    public function testPurchaseUnknownProduct(): void
    {
        $payload = [
            'product' => 999,
            'taxNumber' => 'DE123456789',
            'paymentProcessor' => 'paypal',
        ];

        $this->request($payload);

        self::assertResponseStatusCodeSame(422);

        $data = $this->decodeResponse();
        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);
        $this->assertEquals('Product not found', $data['errors'][0]);
    }

    public function testPurchaseInvalidCoupon(): void
    {
        /** @var Product $product */
        $product = $this->references['product_iphone'];

        $payload = [
            'product' => $product->getId(),
            'taxNumber' => 'DE123456789',
            'couponCode' => 'INVALID',
            'paymentProcessor' => 'paypal',
        ];

        $this->request($payload);

        self::assertResponseStatusCodeSame(422);

        $data = $this->decodeResponse();
        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);
        $this->assertEquals('Invalid or inactive coupon', $data['errors'][0]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function request(array $payload): void
    {
        $json = json_encode($payload) ?: '';

        $this->client->request(
            'POST',
            '/api/purchase',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $json
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeResponse(): array
    {
        $content = (string) $this->client->getResponse()->getContent();
        $decoded = json_decode($content, true);

        if (!is_array($decoded)) {
            $this->fail('Invalid JSON response');
        }

        $decodedStringKeys = [];
        foreach ($decoded as $key => $value) {
            $decodedStringKeys[(string) $key] = $value;
        }

        return $decodedStringKeys;
    }

    #[\Override]
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }
}
