<?php

namespace Keepsuit\LaravelTemporal\Testing;

use Closure;

class TemporalMocker
{
    public function __construct(protected TemporalMockerCache $cache)
    {
    }

    public function clear(): void
    {
        $this->cache->clear();
    }

    public function mockWorkflowResult(string $workflowName, mixed $workflowResult, ?string $taskQueue = null): void
    {
        $result = $workflowResult instanceof Closure ? $workflowResult() : $workflowResult;

        $this->cache->saveWorkflowMock($workflowName, $result, $taskQueue);
    }

    public function getWorkflowResult(string $workflowName, string $taskQueue): ?Closure
    {
        return $this->cache->getWorkflowMock($workflowName, $taskQueue);
    }

    public function mockActivityResult(string $activityName, mixed $activityResult, ?string $taskQueue = null): void
    {
        $result = $activityResult instanceof Closure ? $activityResult() : $activityResult;

        $this->cache->saveActivityMock($activityName, $result, $taskQueue);
    }

    public function getActivityResult(string $activityName, string $taskQueue): ?Closure
    {
        return $this->cache->getActivityMock($activityName, $taskQueue);
    }

    public function recordWorkflowDispatch(string $workflowName, string $taskQueue, array $args): void
    {
        $this->cache->recordWorkflowDispatch($workflowName, $taskQueue, $args);
    }

    public function getWorkflowDispatches(string $workflowName): array
    {
        return $this->cache->getWorkflowDispatches($workflowName);
    }

    public function recordActivityDispatch(string $activityName, string $taskQueue, array $args): void
    {
        $this->cache->recordActivityDispatch($activityName, $taskQueue, $args);
    }

    public function getActivityDispatches(string $activityName): array
    {
        return $this->cache->getActivityDispatches($activityName);
    }
}
