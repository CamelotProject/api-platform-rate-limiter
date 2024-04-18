<?php

declare(strict_types=1);

namespace Camelot\RateLimiter\Provider;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\State\Util\RequestAttributesExtractor;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\Exception\RateLimitExceededException;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class RateLimitProvider implements RateLimitProviderInterface
{
    public const REQUEST_ATTR = '_rate_limit';

    public function __construct(
        private ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        private TokenStorageInterface $tokenStorage,
        /** @var RateLimiterFactory[] */
        #[TaggedIterator('api_platform.rate_limiter', indexAttribute: 'key')]
        private iterable $limiters,
    ) {
        if ($limiters instanceof \Traversable) {
            $this->limiters = iterator_to_array($limiters);
        }
    }

    public function applyHeaders(Request $request): void
    {
        $rateLimit = $request->attributes->get(self::REQUEST_ATTR);
        if (!$rateLimit instanceof RateLimit) {
            return;
        }

        $request->headers->add([
            'RateLimit-Remaining' => $rateLimit->getRemainingTokens(),
            'RateLimit-Reset' => time() - $rateLimit->getRetryAfter()->getTimestamp(),
            'RateLimit-Limit' => $rateLimit->getLimit(),
        ]);
    }

    /** @throws RateLimitExceededException */
    public function consume(Request $request): void
    {
        $factory = $this->getRateLimiterFactory($request);
        if (!$factory) {
            return;
        }

        $user = $this->tokenStorage->getToken()?->getUser();
        if ($user instanceof UserInterface) {
            $limiter = $factory->create(sprintf('rate_limit_user_%s', $user->getUserIdentifier()));
            $limit = $limiter->consume();
            $request->attributes->set('rate_limit', $limit);
            try {
                $limit->ensureAccepted();
            } catch (RateLimitExceededException $e) {
                throw new TooManyRequestsHttpException(message: 'Too many requests. Try again later', previous: $e);
            }

            return;
        }

        $limiter = $factory->create(sprintf('rate_limit_ip_%s', $request->getClientIp()));
        $limit = $limiter->consume();
        $request->attributes->set(self::REQUEST_ATTR, $limit);
        try {
            $limit->ensureAccepted();
        } catch (RateLimitExceededException $e) {
            throw new TooManyRequestsHttpException(message: 'Too many requests. Try again later', previous: $e);
        }
    }

    private function getRateLimiterFactory(Request $request): ?RateLimiterFactory
    {
        if (!$attributes = RequestAttributesExtractor::extractAttributes($request)) {
            return null;
        }

        $resourceMetadata = $this->resourceMetadataCollectionFactory->create($attributes['resource_class']);
        $operation = $resourceMetadata->getOperation($attributes['operation_name']);
        $extra = $operation->getExtraProperties();

        $rateLimiter = $extra['rate_limiter'] ?? null;
        if (!$rateLimiter) {
            return null;
        }

        $key = sprintf('limiter.%s', $rateLimiter);

        return $this->limiters[$key] ?? null;
    }
}
