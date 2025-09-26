<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Enum\TaxRate;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class TaxNumberValidator extends ConstraintValidator
{
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

    private function addViolation(Constraint $constraint): void
    {
        $this->context->buildViolation($constraint->message)
            ->addViolation();
    }
}
