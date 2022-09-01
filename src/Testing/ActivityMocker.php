<?php

namespace Keepsuit\LaravelTemporal\Testing;

use Keepsuit\LaravelTemporal\Testing\Internal\RoadRunnerActivityInvocationCache;
use Temporal\Worker\ActivityInvocationCache\ActivityInvocationCacheInterface;

final class ActivityMocker
{
    private ActivityInvocationCacheInterface $cache;

    public function __construct(
        ActivityInvocationCacheInterface $cache = null,
    ) {
        $this->cache = $cache ?? RoadRunnerActivityInvocationCache::create();
    }

    public function clear(): void
    {
        $this->cache->clear();
    }

    public function expectCompletion(string $activityMethodName, mixed $value): void
    {
        $this->cache->saveCompletion($activityMethodName, $value);
    }

    public function expectFailure(string $activityMethodName, \Throwable $error): void
    {
        $this->cache->saveFailure($activityMethodName, $error);
    }
}
