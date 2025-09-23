<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\PurchaseRequest;
use App\Service\ShopService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/purchase', methods: ['POST'])]
final readonly class PurchaseController
{
    public function __construct(private ShopService $shopService) {}

    public function __invoke(PurchaseRequest $dto): JsonResponse
    {
        try {
            $order = $this->shopService->purchase($dto);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        }

        return new JsonResponse([
            'orderId' => $order->getId(),
            'total' => $order->getTotal()->getCents(),
            'currency' => $order->getTotal()->getCurrency(),
        ]);
    }
}
