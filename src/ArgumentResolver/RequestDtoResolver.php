<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\DTO\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class RequestDtoResolver implements ArgumentValueResolverInterface
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
    ) {
        //
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return is_subclass_of($argument->getType(), RequestDtoInterface::class);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $data = $request->getContent();

        try {
            $dto = $this->serializer->deserialize($data, $argument->getType(), 'json');
        } catch (\Throwable $e) {
            throw new BadRequestHttpException('Invalid JSON: ' . $e->getMessage());
        }

        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            throw new UnprocessableEntityHttpException(json_encode($errors));
        }

        yield $dto;
    }
}
