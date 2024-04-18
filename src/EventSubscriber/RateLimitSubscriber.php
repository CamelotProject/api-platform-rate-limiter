<?php

declare(strict_types=1);

namespace Camelot\RateLimiter\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use Camelot\RateLimiter\Provider\RateLimitProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsTaggedItem('kernel.event_subscriber')]
class RateLimitSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RateLimitProviderInterface $rateLimitProvider,
    ) {}

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $this->rateLimitProvider->consume($event->getRequest());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', EventPriorities::PRE_READ],
        ];
    }
}
