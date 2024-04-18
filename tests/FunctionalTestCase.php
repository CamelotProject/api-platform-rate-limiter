<?php

declare(strict_types=1);

namespace Camelot\RateLimiter\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Camelot\RateLimiter\Tests\Fixtures\Kernel;

/** @internal */
abstract class FunctionalTestCase extends ApiTestCase
{
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }
}
