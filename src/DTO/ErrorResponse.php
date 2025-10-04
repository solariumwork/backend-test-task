<?php

declare(strict_types=1);

namespace App\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'ErrorResponse',
    description: 'Standardized error response',
    type: 'object'
)]
final readonly class ErrorResponse implements \JsonSerializable
{
    /**
     * @var array<int|string, string>
     */
    #[OA\Property(description: 'Validation or processing errors', type: 'array', items: new OA\Items(type: 'string'))]
    public array $errors;

    /**
     * @param array<int|string, string> $errors
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    /**
     * @return array{errors: array<int|string, string>}
     */
    #[\Override]
    public function jsonSerialize(): array
    {
        return ['errors' => $this->errors];
    }
}
