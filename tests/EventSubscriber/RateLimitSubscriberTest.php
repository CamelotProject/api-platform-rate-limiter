<?php

declare(strict_types=1);

namespace Camelot\RateLimiter\Tests\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use Camelot\RateLimiter\EventSubscriber\RateLimitSubscriber;
use Camelot\RateLimiter\Provider\RateLimitProviderInterface;
use Camelot\RateLimiter\Tests\FunctionalTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/** @internal */
#[CoversClass(RateLimitSubscriber::class)]
final class RateLimitSubscriberTest extends FunctionalTestCase
{
    public function testGetSubscribedEvents(): void
    {
        $expected = ['kernel.request' => ['onKernelRequest', EventPriorities::PRE_READ]];
        self::assertSame($expected, RateLimitSubscriber::getSubscribedEvents());
    }

    public function testOnKernelRequest(): void
    {
        $subscriber = $this->getRateLimitSubscriber(self::once(), self::never());
        $request = Request::create('/');
        $event = new RequestEvent(self::createKernel(), $request, HttpKernelInterface::MAIN_REQUEST);
        $subscriber->onKernelRequest($event);
    }

    public function testOnKernelSubRequest(): void
    {
        $subscriber = $this->getRateLimitSubscriber(self::never(), self::never());
        $request = Request::create('/');
        $event = new RequestEvent(self::createKernel(), $request, HttpKernelInterface::SUB_REQUEST);
        $subscriber->onKernelRequest($event);
    }

    private function getRateLimitSubscriber(InvocationOrder $consume, InvocationOrder $apply): RateLimitSubscriber
    {
        $provider = $this->createMock(RateLimitProviderInterface::class);
        $provider->expects($consume)
            ->method('consume')
            ->with(self::isInstanceOf(Request::class))
        ;
        $provider->expects($apply)
            ->method('applyHeaders')
            ->with(self::isInstanceOf(Request::class))
        ;

        return new RateLimitSubscriber($provider);
    }
}
