<?php

declare(strict_types=1);

namespace Camelot\RateLimiter\Tests\Fixtures\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;

#[ApiResource]
#[Get(
    provider: self::class . '::provideItem',
    extraProperties: ['rate_limiter' => 'fixed_window_5_requests_every_10_minutes'],
)]
#[GetCollection(
    provider: self::class . '::provideCollection',
    extraProperties: ['rate_limiter' => 'fixed_window_5_requests_every_10_minutes'],
)]
final class ResourceFixture
{
    public int $id = 0;
    public string $name = '';

    public static function provideItem(Operation $operation, array $uriVariables): self
    {
        $self = new self();
        $self->id = 1;
        $self->name = 'Sonja';

        return $self;
    }

    public static function provideCollection(Operation $operation, array $uriVariables): array
    {
        return [self::provideItem($operation, $uriVariables)];
    }
}
