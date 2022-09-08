<?php

namespace Keepsuit\LaravelTemporal\Testing;

use Closure;
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner\KeyValue\Factory;
use Spiral\RoadRunner\KeyValue\StorageInterface;

final class TemporalMockerCache
{
    private const CACHE_NAME = 'test';

    private StorageInterface $cache;

    public function __construct(string $host, string $cacheName)
    {
        $this->cache = (new Factory(RPC::create($host)))->select($cacheName);
    }

    public static function create(): self
    {
        return new self('tcp://127.0.0.1:6001', self::CACHE_NAME);
    }

    public function clear(): void
    {
        $this->cache->clear();
    }

    public function saveWorkflowMock(string $workflowName, mixed $value): void
    {
        $this->cache->set(sprintf('workflow::%s', $workflowName), $value === null ? 'null' : $value);
    }

    public function getWorkflowMock(string $workflowName): ?Closure
    {
        $value = $this->cache->get(sprintf('workflow::%s', $workflowName));

        return match ($value) {
            'null' => fn () => null,
            null => null,
            default => fn () => $value
        };
    }

    public function saveActivityMock(string $activityName, mixed $value): void
    {
        $this->cache->set(sprintf('activity::%s', $activityName), $value === null ? 'null' : $value);
    }

    public function getActivityMock(string $activityName): ?Closure
    {
        $value = $this->cache->get(sprintf('activity::%s', $activityName));

        return match ($value) {
            'null' => fn () => null,
            null => null,
            default => fn () => $value
        };
    }

    public function recordWorkflowDispatch(string $workflowName, array $args): void
    {
        $cacheKey = sprintf('workflow_dispatch::%s', $workflowName);

        /** @var array $dispatches */
        $dispatches = $this->cache->get($cacheKey, []);

        $dispatches[] = $args;

        $this->cache->set($cacheKey, $dispatches);
    }

    public function getWorkflowDispatches(string $workflowName): array
    {
        return $this->cache->get(sprintf('workflow_dispatch::%s', $workflowName), []);
    }

    public function recordActivityDispatch(string $activityName, array $args): void
    {
        $cacheKey = sprintf('activity_dispatch::%s', $activityName);

        /** @var array $dispatches */
        $dispatches = $this->cache->get($cacheKey, []);

        $dispatches[] = $args;

        $this->cache->set($cacheKey, $dispatches);
    }

    public function getActivityDispatches(string $activityName): array
    {
        return $this->cache->get(sprintf('activity_dispatch::%s', $activityName), []);
    }
}
