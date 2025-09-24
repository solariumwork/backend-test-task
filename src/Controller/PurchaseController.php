<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\PurchaseRequest;
use App\Service\ShopService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/purchase', methods: ['POST'])]
final readonly class PurchaseController
{
    public function __construct(private ShopService $shopService) {}

    #[OA\Post(
        path: '/purchase',
        summary: 'Make a purchase for a product',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: PurchaseRequest::class)
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns the created order',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'orderId', description: 'Unique order ID', type: 'string'),
                        new OA\Property(property: 'total', description: 'Total price in cents', type: 'integer'),
                        new OA\Property(property: 'currency', description: 'Currency code', type: 'string')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation or purchase error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', description: 'Error message', type: 'string')
                    ],
                    type: 'object'
                )
            )
        ]
    )]
    public function __invoke(PurchaseRequest $dto): JsonResponse
    {
        $order = $this->shopService->purchase($dto);

        return new JsonResponse([
            'orderId' => $order->getId(),
            'total' => $order->getTotal()->getCents(),
            'currency' => $order->getTotal()->getCurrency(),
        ]);
    }
}
