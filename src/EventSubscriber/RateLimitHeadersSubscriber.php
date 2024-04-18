<?php

declare(strict_types=1);

namespace Camelot\RateLimiter\EventSubscriber;

use Camelot\RateLimiter\Provider\RateLimitProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RateLimitHeadersSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RateLimitProviderInterface $rateLimitProvider,
    ) {}

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $this->rateLimitProvider->applyHeaders($event->getRequest());
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => 'onKernelResponse'];
    }
}
