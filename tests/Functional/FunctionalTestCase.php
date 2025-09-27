<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class FunctionalTestCase extends WebTestCase
{
    protected static bool $dbInitialized = false;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        if (!self::$dbInitialized) {
            require __DIR__.'/../bootstrap.php';
            self::$dbInitialized = true;
        }
    }
}
