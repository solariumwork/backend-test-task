<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CalculatePriceRequest;
use App\Service\ShopService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/calculate-price', methods: ['POST'])]
final readonly class CalculatePriceController
{
    public function __construct(private ShopService $shopService) {}

    #[OA\Post(
        path: '/calculate-price',
        summary: 'Calculate total price for a product',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: CalculatePriceRequest::class)
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns calculated price',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'price', description: 'Total price in cents', type: 'integer'),
                        new OA\Property(property: 'currency', description: 'Currency code', type: 'string')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation or calculation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', description: 'Error message', type: 'string')
                    ],
                    type: 'object'
                )
            )
        ]
    )]
    public function __invoke(CalculatePriceRequest $dto): JsonResponse
    {
        try {
            $total = $this->shopService->calculatePrice($dto);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        }

        return new JsonResponse([
            'price' => $total->getCents(),
            'currency' => $total->getCurrency(),
        ]);
    }
}
