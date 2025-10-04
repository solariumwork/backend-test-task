<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CalculatePriceRequest;
use App\DTO\CalculatePriceResponse;
use App\DTO\ErrorResponse;
use App\Service\ShopServiceInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/** @psalm-suppress UnusedClass */
#[Route('/api/calculate-price', name: 'calculate_price', methods: ['POST'])]
final readonly class CalculatePriceController
{
    public function __construct(private ShopServiceInterface $shopService)
    {
    }

    #[OA\Post(
        description: 'Calculates the total price including taxes and optional coupon discounts.',
        summary: 'Calculate total price for a product',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: CalculatePriceRequest::class))
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Calculated price and currency',
                content: new OA\JsonContent(ref: new Model(type: CalculatePriceResponse::class))
            ),
            new OA\Response(
                response: Response::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation errors',
                content: new OA\JsonContent(ref: new Model(type: ErrorResponse::class))
            ),
        ]
    )]
    /**
     * Calculates the price for a product and returns it as JSON.
     *
     * @throws \Throwable handled by ApiExceptionSubscriber and returned as 422
     */
    public function __invoke(CalculatePriceRequest $dto): JsonResponse
    {
        $total = $this->shopService->calculatePrice($dto);

        return new JsonResponse(new CalculatePriceResponse($total), Response::HTTP_OK);
    }
}
