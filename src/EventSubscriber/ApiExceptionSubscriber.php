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

class ApiExceptionSubscriber implements EventSubscriberInterface
{
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
        $response = [
            'errors' => [$exception->getMessage()],
        ];

        $statusCode = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : Response::HTTP_UNPROCESSABLE_ENTITY;

        $event->setResponse(new JsonResponse($response, $statusCode));
    }

    private function isApiRequest(Request $request): bool
    {
        $contentType = strtolower($request->headers->get('Content-Type', ''));
        $acceptHeader = strtolower($request->headers->get('Accept', ''));

        return str_contains($acceptHeader, 'application/json')
            || str_contains($contentType, 'application/json');
    }
}
