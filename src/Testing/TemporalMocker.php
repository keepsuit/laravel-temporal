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

    public function mockWorkflowResult(string $workflowName, mixed $workflowResult): void
    {
        $result = $workflowResult instanceof Closure ? $workflowResult() : $workflowResult;

        $this->cache->saveWorkflowMock($workflowName, $result);
    }

    public function getWorkflowResult(string $workflowName): ?Closure
    {
        return $this->cache->getWorkflowMock($workflowName);
    }

    public function mockActivityResult(string $activityName, mixed $activityResult): void
    {
        $result = $activityResult instanceof Closure ? $activityResult() : $activityResult;

        $this->cache->saveActivityMock($activityName, $result);
    }

    public function getActivityResult(string $activityName): ?Closure
    {
        return $this->cache->getActivityMock($activityName);
    }

    public function recordWorkflowDispatch(string $workflowName, array $args): void
    {
        $this->cache->recordWorkflowDispatch($workflowName, $args);
    }

    public function getWorkflowDispatches(string $workflowName): array
    {
        return $this->cache->getWorkflowDispatches($workflowName);
    }

    public function recordActivityDispatch(string $activityName, array $args): void
    {
        $this->cache->recordActivityDispatch($activityName, $args);
    }

    public function getActivityDispatches(string $activityName): array
    {
        return $this->cache->getActivityDispatches($activityName);
    }
}
