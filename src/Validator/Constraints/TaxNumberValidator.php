<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Tax\Enum\TaxRate;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/** @psalm-suppress UnusedClass */
final class TaxNumberValidator extends ConstraintValidator
{
    #[\Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof TaxNumber || $this->isEmpty($value)) {
            return;
        }

        if (!is_string($value)) {
            $this->addViolation($constraint);

            return;
        }

        try {
            TaxRate::fromTaxNumber($value);
        } catch (\InvalidArgumentException) {
            $this->addViolation($constraint);
        }
    }

    private function isEmpty(mixed $value): bool
    {
        return null === $value || '' === $value;
    }

    private function addViolation(TaxNumber $constraint): void
    {
        $this->context->buildViolation($constraint->message)
            ->addViolation();
    }
}
