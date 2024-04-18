<?php

declare(strict_types=1);

namespace Camelot\RateLimiter;

use Camelot\RateLimiter\DependencyInjection\Compiler\RateLimiterCompilerPass;
use Camelot\RateLimiter\EventSubscriber\RateLimitHeadersSubscriber;
use Camelot\RateLimiter\EventSubscriber\RateLimitSubscriber;
use Camelot\RateLimiter\Provider\RateLimitProvider;
use Camelot\RateLimiter\Provider\RateLimitProviderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class RateLimiterBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RateLimiterCompilerPass());
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $services = $container->services();
        $services->defaults()
            ->autoconfigure()
            ->autowire()
        ;

        $services->set(RateLimitProvider::class);
        $services->alias(RateLimitProviderInterface::class, RateLimitProvider::class);

        $services->set(RateLimitHeadersSubscriber::class);
        $services->set(RateLimitSubscriber::class);
    }
}
