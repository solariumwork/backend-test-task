<?php

declare(strict_types=1);

namespace App\Enum;

enum TaxRate: string
{
    case GERMANY = '0.19';
    case ITALY   = '0.22';
    case FRANCE  = '0.20';
    case GREECE  = '0.24';

    public static function fromTaxNumber(string $taxNumber): float
    {
        return (float) match (true) {
            preg_match('/^DE\d{9}$/', $taxNumber) === 1 => self::GERMANY->value,
            preg_match('/^IT\d{11}$/', $taxNumber) === 1 => self::ITALY->value,
            preg_match('/^FR[A-Z]{2}\d{9}$/', $taxNumber) === 1 => self::FRANCE->value,
            preg_match('/^GR\d{9}$/', $taxNumber) === 1 => self::GREECE->value,
            default => throw new \InvalidArgumentException('Invalid tax number'),
        };
    }
}
