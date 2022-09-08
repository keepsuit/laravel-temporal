<?php

namespace Keepsuit\LaravelTemporal\Testing;

use Closure;
use Keepsuit\LaravelTemporal\Testing\Internal\RoadRunnerActivityInvocationCache;
use Throwable;

class TemporalMocker
{
    public function __construct(protected RoadRunnerActivityInvocationCache $cache)
    {
    }

    public function clear(): void
    {
        $this->cache->clear();
    }

    public function mockWorkflowResult(string $workflowName, mixed $workflowResult): void
    {
        $result = $workflowResult instanceof Closure ? $workflowResult : fn () => $workflowResult;

        $this->cache->saveWorkflowMock($workflowName, $result);
    }

    public function getWorkflowResult(string $workflowName): ?Closure
    {
        return $this->cache->getWorkflowMock($workflowName);
    }

    public function mockActivityResult(string $activityName, mixed $activityResult): void
    {
        if ($activityResult instanceof Closure || is_callable($activityResult)) {
            try {
                $this->cache->saveCompletion($activityName, $activityResult());
            } catch (\Exception $exception) {
                $this->cache->saveFailure($activityName, $exception);
            }

            return;
        }

        if ($activityResult instanceof Throwable) {
            $this->cache->saveFailure($activityName, $activityResult);

            return;
        }

        $this->cache->saveCompletion($activityName, $activityResult);
    }

    public function recordWorkflowDispatch(string $workflowName, array $args): void
    {
        $this->cache->recordWorkflowDispatch($workflowName, $args);
    }

    public function getWorkflowDispatches(string $workflowName): array
    {
        return $this->cache->getWorkflowDispatches($workflowName);
    }
}
