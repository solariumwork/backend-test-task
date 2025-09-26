<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\DTO\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/** @psalm-suppress UnusedClass */
final readonly class RequestDtoResolver implements ArgumentValueResolverInterface
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
    ) {
        //
    }

    #[\Override]
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        $type = $argument->getType();

        return is_string($type) && is_subclass_of($type, RequestDtoInterface::class);
    }

    /**
     * @return iterable<RequestDtoInterface>
     */
    #[\Override]
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $this->getDtoTypeOrFail($argument);
        $dto = $this->deserializeDto($request->getContent(), $type);

        $this->normalizeStringProperties($dto);
        $this->validateDto($dto);

        yield $dto;
    }

    private function getDtoTypeOrFail(ArgumentMetadata $argument): string
    {
        $type = $argument->getType();
        if (!is_string($type)) {
            throw new BadRequestHttpException('Missing or invalid DTO type for argument.');
        }

        return $type;
    }

    private function deserializeDto(string $data, string $type): RequestDtoInterface
    {
        try {
            $dto = $this->serializer->deserialize($data, $type, 'json');
        } catch (\Throwable $e) {
            throw new BadRequestHttpException('Invalid JSON: ' . $e->getMessage());
        }

        if (!$dto instanceof RequestDtoInterface) {
            throw new BadRequestHttpException('Deserialized object is not a valid request DTO.');
        }

        return $dto;
    }

    private function normalizeStringProperties(object $dto): void
    {
        foreach (get_object_vars($dto) as $property => $value) {
            if (is_string($value)) {
                $dto->$property = trim($value);
            }
        }
    }

    private function validateDto(RequestDtoInterface $dto): void
    {
        $violations = $this->validator->validate($dto);
        if (count($violations) === 0) {
            return;
        }

        $errors = $this->mapViolationsToArray($violations);
        throw new UnprocessableEntityHttpException((string) json_encode($errors));
    }

    /**
     * @param ConstraintViolationListInterface $violations
     * @return array<string, string>
     */
    private function mapViolationsToArray(ConstraintViolationListInterface $violations): array
    {
        $errors = [];
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }

        return $errors;
    }
}
