<?php

declare(strict_types=1);

namespace Camelot\RateLimiter\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RateLimiterCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $definition) {
            if ($definition instanceof ChildDefinition && $definition->getParent() === 'limiter') {
                $definition->addTag('api_platform.rate_limiter');
            }
        }
    }
}
