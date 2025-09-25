<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;

final readonly class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger)
    {
        //
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onKernelException'];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (!$this->isApiRequest($request)) {
            return;
        }

        $exception = $event->getThrowable();

        match (true) {
            $exception instanceof UnprocessableEntityHttpException => $this->handleUnprocessableEntity($event, $exception),
            $exception instanceof HttpExceptionInterface => $this->handleHttpException($event, $exception),
            $exception instanceof \InvalidArgumentException => $this->respond(
                $event,
                Response::HTTP_UNPROCESSABLE_ENTITY,
                ['error' => $exception->getMessage()]
            ),
            $exception instanceof \RuntimeException => $this->respond(
                $event,
                Response::HTTP_UNPROCESSABLE_ENTITY,
                ['error' => 'Payment failed']
            ),
            default => $this->respond(
                $event,
                Response::HTTP_UNPROCESSABLE_ENTITY,
                ['error' => 'Unexpected error occurred'],
                $exception
            ),
        };
    }

    private function handleUnprocessableEntity(ExceptionEvent $event, UnprocessableEntityHttpException $exception): void
    {
        $decoded = json_decode($exception->getMessage(), true) ?: [];
        $payload = $decoded !== []
            ? ['errors' => $decoded]
            : ['error' => $exception->getMessage() ?: 'Unprocessable Entity'];

        $this->respond($event, Response::HTTP_UNPROCESSABLE_ENTITY, $payload);
    }

    private function handleHttpException(ExceptionEvent $event, HttpExceptionInterface $exception): void
    {
        $status = $exception->getStatusCode();
        $payload = $status >= 500
            ? ['error' => 'Unexpected error occurred']
            : ['error' => $exception->getMessage() ?: (Response::$statusTexts[$status] ?? 'Error')];

        $responseStatus = $status >= 500 ? Response::HTTP_UNPROCESSABLE_ENTITY : $status;

        $this->respond($event, $responseStatus, $payload, $status >= 500 ? $exception : null);
    }

    private function respond(ExceptionEvent $event, int $status, array $payload, ?Throwable $exception = null): void
    {
        $event->setResponse(new JsonResponse($payload, $status));

        if ($exception !== null) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
        }
    }

    private function isApiRequest(Request $request): bool
    {
        $contentType = strtolower($request->headers->get('Content-Type', ''));
        $acceptHeader = strtolower($request->headers->get('Accept', ''));

        return str_contains($contentType, 'application/json')
            || str_contains($acceptHeader, 'application/json');
    }
}
