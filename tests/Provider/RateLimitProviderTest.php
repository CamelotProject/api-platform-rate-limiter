<?php

declare(strict_types=1);

namespace Camelot\RateLimiter\Tests\Provider;

use ApiPlatform\Symfony\Bundle\Test\Client;
use Camelot\RateLimiter\Provider\RateLimitProvider;
use Camelot\RateLimiter\Tests\FunctionalTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimit;

/** @internal */
#[CoversClass(RateLimitProvider::class)]
final class RateLimitProviderTest extends FunctionalTestCase
{
    public function testConsume(): void
    {
        self::clearCache();

        $client = self::createClient();

        $this->request($client, 200);
        $this->request($client, 200);
        $this->request($client, 200);
        $this->request($client, 200);
        $this->request($client, 200);

        $this->request($client, 429);
    }

    public function testApplyHeaders(): void
    {
        $container = self::getContainer();
        $provider = $container->get(RateLimitProvider::class);
        $limit = new RateLimit(3, new \DateTimeImmutable('+5 min'), true, 10);
        $request = Request::create('/');
        $request->attributes->set(RateLimitProvider::REQUEST_ATTR, $limit);

        $provider->applyHeaders($request);
        $headers = $request->headers;

        self::assertTrue($headers->has('RateLimit-Remaining'));
        self::assertTrue($headers->has('RateLimit-Reset'));
        self::assertTrue($headers->has('RateLimit-Limit'));

        self::assertSame('3', $headers->get('RateLimit-Remaining'));
        self::assertSame('10', $headers->get('RateLimit-Limit'));
    }

    public function testDoNotApplyHeaders(): void
    {
        $container = self::getContainer();
        $provider = $container->get(RateLimitProvider::class);
        $request = Request::create('/');

        $provider->applyHeaders($request);
        $headers = $request->headers;

        self::assertFalse($headers->has('RateLimit-Remaining'));
        self::assertFalse($headers->has('RateLimit-Reset'));
        self::assertFalse($headers->has('RateLimit-Limit'));
    }

    private function request(Client $client, int $expectStatusCode): void
    {
        $response = $client->request('GET', '/resource_fixtures');
        self::assertSame($expectStatusCode, $response->getStatusCode());
    }

    private static function clearCache(): void
    {
        $container = self::getContainer();
        $container->get('cache.rate_limiter')->clear();
    }
}
