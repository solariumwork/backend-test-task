<?php

declare(strict_types=1);

namespace App\Enum;

enum TaxRate: string
{
    case GERMANY = '0.19';
    case ITALY = '0.22';
    case FRANCE = '0.20';
    case GREECE = '0.24';

    public static function fromTaxNumber(string $taxNumber): float
    {
        return (float) match (true) {
            1 === preg_match('/^DE\d{9}$/', $taxNumber) => self::GERMANY->value,
            1 === preg_match('/^IT\d{11}$/', $taxNumber) => self::ITALY->value,
            1 === preg_match('/^FR[A-Z]{2}\d{9}$/', $taxNumber) => self::FRANCE->value,
            1 === preg_match('/^GR\d{9}$/', $taxNumber) => self::GREECE->value,
            default => throw new \InvalidArgumentException('Invalid tax number'),
        };
    }
}
