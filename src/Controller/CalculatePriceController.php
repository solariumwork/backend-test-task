<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CalculatePriceRequest;
use App\Service\ShopService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/calculate-price', methods: ['POST'])]
final readonly class CalculatePriceController
{
    public function __construct(private ShopService $shopService) {}

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
