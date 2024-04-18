<?php

declare(strict_types=1);

namespace Camelot\RateLimiter\Tests\DependencyInjection\Compiler;

use Camelot\RateLimiter\DependencyInjection\Compiler\RateLimiterCompilerPass;
use Camelot\RateLimiter\Tests\FunctionalTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\RateLimiter\RateLimiterFactory;

/** @internal */
#[CoversClass(RateLimiterCompilerPass::class)]
final class RateLimiterCompilerPassTest extends FunctionalTestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $limiter = new Definition(RateLimiterFactory::class);
        $limiter->setAbstract(true);
        $container->setDefinition('limiter', $limiter);

        $childLimiter = new ChildDefinition(RateLimiterFactory::class);
        $childLimiter->setParent('limiter');
        $container->setDefinition('limiter.test-child', $childLimiter);

        $pass = new RateLimiterCompilerPass();
        $pass->process($container);

        self::assertFalse($container->getDefinition('limiter')->hasTag('api_platform.rate_limiter'));
        self::assertTrue($container->getDefinition('limiter.test-child')->hasTag('api_platform.rate_limiter'));
    }
}
