<?php

namespace Keepsuit\LaravelTemporal\Testing;

use Closure;
use Illuminate\Support\Arr;
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

    public function saveWorkflowMock(string $workflowName, mixed $value, ?string $taskQueue = null): void
    {
        $this->cache->set(sprintf('workflow::%s', $workflowName), [
            'mock' => $value === null ? 'null' : $value,
            'taskQueue' => $taskQueue,
        ]);
    }

    public function getWorkflowMock(string $workflowName, string $taskQueue): ?Closure
    {
        $value = $this->cache->get(sprintf('workflow::%s', $workflowName));

        if (! is_array($value) || ! Arr::has($value, 'mock')) {
            return null;
        }

        if (Arr::get($value, 'taskQueue') !== null && $value['taskQueue'] !== $taskQueue) {
            return null;
        }

        return match (Arr::get($value, 'mock')) {
            'null' => fn () => null,
            null => null,
            default => fn () => $value['mock']
        };
    }

    public function saveActivityMock(string $activityName, mixed $value, ?string $taskQueue = null): void
    {
        $this->cache->set(sprintf('activity::%s', $activityName), [
            'mock' => $value === null ? 'null' : $value,
            'taskQueue' => $taskQueue,
        ]);
    }

    public function getActivityMock(string $activityName, string $taskQueue): ?Closure
    {
        $value = $this->cache->get(sprintf('activity::%s', $activityName));

        if (! is_array($value) || ! Arr::has($value, 'mock')) {
            return null;
        }

        if (Arr::get($value, 'taskQueue') !== null && $value['taskQueue'] !== $taskQueue) {
            return null;
        }

        return match (Arr::get($value, 'mock')) {
            'null' => fn () => null,
            null => null,
            default => fn () => $value['mock']
        };
    }

    public function recordWorkflowDispatch(string $workflowName, string $taskQueue, array $args): void
    {
        $cacheKey = sprintf('workflow_dispatch::%s', $workflowName);

        /** @var array $dispatches */
        $dispatches = $this->cache->get($cacheKey, []);

        $dispatches[] = [
            'taskQueue' => $taskQueue,
            'args' => $args,
        ];

        $this->cache->set($cacheKey, $dispatches);
    }

    public function getWorkflowDispatches(string $workflowName): array
    {
        return $this->cache->get(sprintf('workflow_dispatch::%s', $workflowName), []);
    }

    public function recordActivityDispatch(string $activityName, string $taskQueue, array $args): void
    {
        $cacheKey = sprintf('activity_dispatch::%s', $activityName);

        /** @var array $dispatches */
        $dispatches = $this->cache->get($cacheKey, []);

        $dispatches[] = [
            'taskQueue' => $taskQueue,
            'args' => $args,
        ];

        $this->cache->set($cacheKey, $dispatches);
    }

    public function getActivityDispatches(string $activityName): array
    {
        return $this->cache->get(sprintf('activity_dispatch::%s', $activityName), []);
    }
}
