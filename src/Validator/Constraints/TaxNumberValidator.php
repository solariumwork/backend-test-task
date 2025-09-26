<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Enum\TaxRate;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class TaxNumberValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof TaxNumber) {
            return;
        }

        try {
            TaxRate::fromTaxNumber($value);
        } catch (\InvalidArgumentException) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
