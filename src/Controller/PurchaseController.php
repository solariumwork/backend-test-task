<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\ErrorResponse;
use App\DTO\PurchaseRequest;
use App\DTO\PurchaseResponse;
use App\Service\ShopServiceInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/** @psalm-suppress UnusedClass */
#[Route('/api/purchase', name: 'purchase', methods: ['POST'])]
final readonly class PurchaseController
{
    public function __construct(private ShopServiceInterface $shopService)
    {
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
                description: 'Order details including ID, total amount, and currency',
                content: new OA\JsonContent(ref: new Model(type: PurchaseResponse::class))
            ),
            new OA\Response(
                response: Response::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation errors',
                content: new OA\JsonContent(ref: new Model(type: ErrorResponse::class))
            ),
        ]
    )]
    /**
     * Processes a purchase for a product and returns order details as JSON.
     *
     * @throws \Throwable handled by ApiExceptionSubscriber and returned as 422
     */
    public function __invoke(PurchaseRequest $dto): JsonResponse
    {
        $order = $this->shopService->purchase($dto);

        return new JsonResponse(PurchaseResponse::fromOrder($order), Response::HTTP_OK);
    }
}
