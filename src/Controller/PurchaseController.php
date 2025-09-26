<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\PurchaseRequest;
use App\Service\ShopServiceInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/** @psalm-suppress UnusedClass */
#[Route('/api/purchase', name: 'purchase', methods: ['POST'])]
final readonly class PurchaseController
{
    public function __construct(private ShopServiceInterface $shopService)
    {
        //
    }

    #[OA\Post(
        description: 'Processes a purchase, applying taxes and optional coupon discounts.',
        summary: 'Make a purchase for a product',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: PurchaseRequest::class))
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Returns the order details including ID, total amount, and currency',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'orderId', description: 'Unique ID of the order', type: 'integer', example: 101),
                        new OA\Property(property: 'total', description: 'Total amount in euros', type: 'number', format: 'float', example: 49.99),
                        new OA\Property(property: 'currency', description: 'Currency code', type: 'string', example: 'EUR'),
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
    /**
     * Processes a purchase for a product and returns order details as JSON.
     *
     * Delegates the business logic to ShopService. Exceptions such as invalid product,
     * invalid coupon, or payment failure are caught by ApiExceptionSubscriber and returned
     * as JSON with status 422 (Unprocessable Entity).
     *
     * @param PurchaseRequest $dto Validated purchase request DTO
     *
     * @return JsonResponse{
     *     orderId: int,
     *     total: float,
     *     currency: string
     * }
     *
     * @throws \Throwable Only unexpected errors; all known exceptions are handled globally.
     */
    public function __invoke(PurchaseRequest $dto): JsonResponse
    {
        $order = $this->shopService->purchase($dto);

        return new JsonResponse([
            'orderId' => $order->getId(),
            'total' => $order->getTotal()->getEuros(),
            'currency' => $order->getTotal()->getCurrency(),
        ]);
    }
}
