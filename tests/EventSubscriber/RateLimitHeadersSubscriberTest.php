<?php

declare(strict_types=1);

namespace Camelot\RateLimiter\Tests\EventSubscriber;

use Camelot\RateLimiter\EventSubscriber\RateLimitHeadersSubscriber;
use Camelot\RateLimiter\Provider\RateLimitProviderInterface;
use Camelot\RateLimiter\Tests\FunctionalTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/** @internal */
#[CoversClass(RateLimitHeadersSubscriber::class)]
final class RateLimitHeadersSubscriberTest extends FunctionalTestCase
{
    public function testGetSubscribedEvents(): void
    {
        $expected = [KernelEvents::RESPONSE => 'onKernelResponse'];
        self::assertSame($expected, RateLimitHeadersSubscriber::getSubscribedEvents());
    }

    public function testOnKernelRequest(): void
    {
        $subscriber = $this->getRateLimitHeadersSubscriber(self::never(), self::once());
        $request = Request::create('/');
        $event = new ResponseEvent(self::createKernel(), $request, HttpKernelInterface::MAIN_REQUEST, new Response());
        $subscriber->onKernelResponse($event);
    }

    public function testOnKernelSubRequest(): void
    {
        $subscriber = $this->getRateLimitHeadersSubscriber(self::never(), self::never());
        $request = Request::create('/');
        $event = new ResponseEvent(self::createKernel(), $request, HttpKernelInterface::SUB_REQUEST, new Response());
        $subscriber->onKernelResponse($event);
    }

    private function getRateLimitHeadersSubscriber(InvocationOrder $consume, InvocationOrder $apply): RateLimitHeadersSubscriber
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

        return new RateLimitHeadersSubscriber($provider);
    }
}
