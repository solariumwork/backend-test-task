<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/** @psalm-suppress UnusedClass */
class ApiExceptionSubscriber implements EventSubscriberInterface
{
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (!$this->isApiRequest($request)) {
            return;
        }

        $exception = $event->getThrowable();
        $errors = $this->normalizeErrors((string) $exception->getMessage());

        $statusCode = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : Response::HTTP_UNPROCESSABLE_ENTITY;

        $event->setResponse(new JsonResponse(['errors' => $errors], $statusCode));
    }

    private function isApiRequest(Request $request): bool
    {
        $contentType = strtolower((string) $request->headers->get('Content-Type', ''));
        $acceptHeader = strtolower((string) $request->headers->get('Accept', ''));

        return str_contains($acceptHeader, 'application/json')
            || str_contains($contentType, 'application/json');
    }

    /**
     * @return array<mixed>
     */
    private function normalizeErrors(string $rawMessage): array
    {
        $decoded = json_decode($rawMessage, true);
        if (JSON_ERROR_NONE === json_last_error() && is_array($decoded)) {
            return $this->isAssoc($decoded) ? $decoded : array_values($decoded);
        }

        return [$rawMessage];
    }

    /**
     * @param array<mixed, mixed> $arr
     */
    private function isAssoc(array $arr): bool
    {
        if ([] === $arr) {
            return false;
        }

        return !array_is_list($arr);
    }
}
