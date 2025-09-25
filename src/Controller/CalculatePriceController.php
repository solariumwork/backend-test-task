<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CalculatePriceRequest;
use App\Service\ShopServiceInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/** @psalm-suppress UnusedClass */
#[Route('/api/calculate-price', methods: ['POST'])]
final readonly class CalculatePriceController
{
    public function __construct(private ShopServiceInterface $shopService)
    {
    }

    #[OA\Post(
        path: '/api/calculate-price',
        description: 'Calculates the total price including taxes and optional coupon discounts.',
        summary: 'Calculate total price for a product',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: CalculatePriceRequest::class))
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Returns the calculated price and currency',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'price', description: 'Total price', type: 'number', format: 'float'),
                        new OA\Property(property: 'currency', description: 'Currency code', type: 'string', example: 'EUR'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: 'Bad Request',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'errors',
                            description: 'List of syntax/structure errors in the request',
                            type: 'array',
                            items: new OA\Items(type: 'string', example: 'Invalid tax number')
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: Response::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Unprocessable Entity',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'errors',
                            description: 'List of validation errors',
                            type: 'array',
                            items: new OA\Items(type: 'string', example: 'total: This value should be greater than 0.')
                        ),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function __invoke(CalculatePriceRequest $dto): JsonResponse
    {
        $total = $this->shopService->calculatePrice($dto);

        return new JsonResponse([
            'price' => $total->getEuros(),
            'currency' => $total->getCurrency(),
        ]);
    }
}
