<?php

namespace Keepsuit\LaravelTemporal\Testing;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner\KeyValue\Factory;
use Spiral\RoadRunner\KeyValue\StorageInterface;

final class TemporalMockerCache
{
    /**
     * @var string
     */
    private const CACHE_NAME = 'test';

    private readonly StorageInterface $cache;

    private readonly Collection $localCache;

    private bool $localOnly = false;

    public function __construct(string $host, string $cacheName)
    {
        $this->cache = (new Factory(RPC::create($host)))->select($cacheName);
        $this->localCache = Collection::make();
    }

    public static function create(): self
    {
        return new self(
            sprintf('tcp://127.0.0.1:%d', config('temporal.rpc_port', 6001)),
            self::CACHE_NAME
        );
    }

    public function clear(): void
    {
        $this->cacheProxy(fn () => $this->cache->clear());
    }

    public function saveWorkflowMock(string $workflowName, mixed $value, ?string $taskQueue = null): void
    {
        $key = sprintf('workflow::%s', $workflowName);

        $payload = [
            'mock' => $value ?? 'null',
            'taskQueue' => $taskQueue,
        ];

        $this->cacheProxy(
            fn () => $this->cache->set($key, $payload),
            fn () => $this->localCache->put($key, $payload)
        );
    }

    public function getWorkflowMock(string $workflowName, string $taskQueue): ?Closure
    {
        $key = sprintf('workflow::%s', $workflowName);

        $value = $this->cacheProxy(
            fn () => $this->cache->get($key),
            fn () => $this->localCache->get($key)
        );

        if (! is_array($value)) {
            return null;
        }

        if (! Arr::has($value, 'mock')) {
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
        $this->cacheProxy(fn () => $this->cache->set(sprintf('activity::%s', $activityName), [
            'mock' => $value ?? 'null',
            'taskQueue' => $taskQueue,
        ]));
    }

    public function getActivityMock(string $activityName, ?string $taskQueue): ?Closure
    {
        $value = $this->cache->get(sprintf('activity::%s', $activityName));

        if (! is_array($value)) {
            return null;
        }

        if (! Arr::has($value, 'mock')) {
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
        $key = sprintf('workflow_dispatch::%s', $workflowName);

        /** @var array $dispatches */
        $dispatches = $this->cacheProxy(
            fn () => $this->cache->get($key, []),
            fn () => $this->localCache->get($key, [])
        );

        $dispatches[] = [
            'taskQueue' => $taskQueue,
            'args' => $args,
        ];

        $this->cacheProxy(
            fn () => $this->cache->set($key, $dispatches),
            fn () => $this->localCache->put($key, $dispatches)
        );
    }

    public function getWorkflowDispatches(string $workflowName): array
    {
        $key = sprintf('workflow_dispatch::%s', $workflowName);

        return $this->cacheProxy(
            fn () => $this->cache->get($key, []),
            fn () => $this->localCache->get($key, [])
        );
    }

    public function recordActivityDispatch(string $activityName, ?string $taskQueue, array $args): void
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

    private function cacheProxy(Closure $action, ?Closure $fallback = null): mixed
    {
        if ($this->localOnly) {
            return $fallback?->__invoke();
        }

        try {
            return retry(5, $action, fn (int $attempt) => $attempt * 100);
        } catch (\Exception) {
            $this->localOnly = true;

            return $fallback?->__invoke();
        }
    }
}
