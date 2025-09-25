<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\Coupon;
use App\Entity\Product;
use App\Kernel;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/** @psalm-suppress UnusedClass */
class CalculatePriceControllerTest extends WebTestCase
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

    public function testCalculatePriceSuccess(): void
    {
        /** @var Product $product */
        $product = $this->references['product_iphone'];
        /** @var Coupon $coupon */
        $coupon = $this->references['coupon_D15'];

        $client = $this->request([
            'product' => $product->getId(),
            'taxNumber' => 'DE123456789',
            'couponCode' => $coupon->getCode(),
        ]);

        self::assertResponseIsSuccessful();
        $data = $this->decodeResponse($client);
        $this->assertArrayHasKey('price', $data);
        $this->assertArrayHasKey('currency', $data);
        $this->assertEquals('EUR', $data['currency']);
        $this->assertGreaterThan(0, $data['price']);
    }

    public function testCalculatePriceInvalidTaxNumber(): void
    {
        /** @var Product $product */
        $product = $this->references['product_iphone'];
        /** @var Coupon $coupon */
        $coupon = $this->references['coupon_D15'];

        $client = $this->request([
            'product' => $product->getId(),
            'taxNumber' => 'INVALID',
            'couponCode' => $coupon->getCode(),
        ]);

        self::assertResponseStatusCodeSame(422);
        $data = $this->decodeResponse($client);
        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);
        $this->assertContains('Invalid tax number', $data['errors']);
    }

    public function testCalculatePriceUnknownProduct(): void
    {
        $client = $this->request([
            'product' => 999,
            'taxNumber' => 'DE123456789',
        ]);

        self::assertResponseStatusCodeSame(422);
        $data = $this->decodeResponse($client);
        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);
        $this->assertContains('Product not found', $data['errors']);
    }

    public function testCalculatePriceProductZero(): void
    {
        $client = $this->request([
            'product' => 0,
            'taxNumber' => 'DE123456789',
        ]);

        self::assertResponseStatusCodeSame(422);
        $data = $this->decodeResponse($client);
        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);
        $this->assertStringContainsString('positive', implode(' ', $data['errors']));
    }

    public function testCalculatePriceEmptyCouponCode(): void
    {
        /** @var Product $product */
        $product = $this->references['product_iphone'];

        $client = $this->request([
            'product' => $product->getId(),
            'taxNumber' => 'DE123456789',
            'couponCode' => '',
        ]);

        self::assertResponseIsSuccessful();
        $data = $this->decodeResponse($client);
        $this->assertArrayHasKey('price', $data);
    }

    public function testCalculatePriceMissingProduct(): void
    {
        $client = $this->request([
            'taxNumber' => 'DE123456789',
        ]);

        self::assertResponseStatusCodeSame(422);
        $data = $this->decodeResponse($client);
        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);
        $this->assertStringContainsString('blank', implode(' ', $data['errors']));
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function request(array $payload): KernelBrowser
    {
        $json = json_encode($payload);
        if (false === $json) {
            $json = '';
        }

        $this->client->request(
            'POST',
            '/api/calculate-price',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $json
        );

        return $this->client;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeResponse(KernelBrowser $client): array
    {
        $decoded = json_decode((string) $client->getResponse()->getContent(), true);
        if (!is_array($decoded)) {
            $this->fail('Invalid JSON response');
        }

        $stringKeyed = [];
        foreach ($decoded as $key => $value) {
            $stringKeyed[(string) $key] = $value;
        }

        return $stringKeyed;
    }

    #[\Override]
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }
}
