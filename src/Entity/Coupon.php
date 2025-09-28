<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CouponRepository;
use Doctrine\ORM\Mapping as ORM;

/** @psalm-suppress UnusedProperty */
#[ORM\Entity(repositoryClass: CouponRepository::class)]
#[ORM\Table(name: 'coupon')]
class Coupon
{
    public const string TYPE_PERCENT = 'percent';
    public const string TYPE_FIXED = 'fixed';

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 50)]
    private string $code;

    #[ORM\Column(type: 'string', length: 10)]
    private string $type;

    /**
     * If percent: integer (e.g. 10 = 10%).
     * If fixed: cents (e.g. 1500).
     */
    #[ORM\Column(type: 'integer')]
    private int $value;

    #[ORM\Column(type: 'boolean')]
    private bool $active = true;

    public function __construct(
        string $code,
        string $type,
        int $value,
        bool $active = true,
    ) {
        if (!in_array($type, [self::TYPE_PERCENT, self::TYPE_FIXED], true)) {
            throw new \InvalidArgumentException('Invalid coupon type.');
        }

        if (self::TYPE_PERCENT === $type && ($value < 0 || $value > 100)) {
            throw new \InvalidArgumentException('Percent coupon value must be between 0 and 100.');
        }

        if (self::TYPE_FIXED === $type && $value < 0) {
            throw new \InvalidArgumentException('Fixed coupon value cannot be negative.');
        }

        $this->code = $code;
        $this->type = $type;
        $this->value = $value;
        $this->active = $active;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    /** @psalm-suppress PossiblyUnusedMethod */
    public function isActive(): bool
    {
        return $this->active;
    }
}
