<?php

declare(strict_types=1);

namespace Camelot\RateLimiter\Tests\Fixtures;

use ApiPlatform\Symfony\Bundle\ApiPlatformBundle;
use Camelot\RateLimiter\RateLimiterBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new ApiPlatformBundle();
        yield new RateLimiterBundle();
        yield new SecurityBundle();
        yield new TwigBundle();
    }

    private function configureContainer(ContainerConfigurator $container, LoaderInterface $loader, ContainerBuilder $builder): void
    {
        $parameters = $container->parameters();
        $parameters->set('kernel.secret', 'hunter42');

        $container->extension('framework', [
            'test' => true,
            'rate_limiter' => [
                'fixed_window_5_requests_every_10_minutes' => [
                    'policy' => 'fixed_window',
                    'limit' => 5,
                    'interval' => '10 minutes',
                ],
            ],
        ]);

        $container->extension('security', [
            'providers' => ['users_in_memory' => ['memory' => null]],
            'firewalls' => [
                'dev' => [
                    'pattern' => '^/(_(profiler|wdt)|css|images|js)/',
                    'security' => false,
                ],
                'main' => [
                    'lazy' => true,
//                    'provider' => 'users_in_memory',
                ],
            ],
        ]);

        $container->extension('api_platform', [
            'resource_class_directories' => [
                __DIR__ . '/ApiResource',
            ],
        ]);
    }

    private function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('.', 'api_platform')->prefix('/');
    }
}
