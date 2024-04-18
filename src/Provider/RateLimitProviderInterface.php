<?php

declare(strict_types=1);

namespace Camelot\RateLimiter\Provider;

use Symfony\Component\HttpFoundation\Request;

interface RateLimitProviderInterface
{
    public function consume(Request $request): void;

    public function applyHeaders(Request $request): void;
}
