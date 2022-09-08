<?php

namespace Keepsuit\LaravelTemporal\Testing\Fakes;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Keepsuit\LaravelTemporal\Temporal;
use Keepsuit\LaravelTemporal\Testing\TemporalMocker;
use PHPUnit\Framework\Assert as PHPUnit;
use Temporal\Client\WorkflowClientInterface;
use Temporal\Workflow;

class TemporalFake extends Temporal
{
    protected bool $activityCacheCleared = false;

    protected TemporalMocker $temporalMocker;

    public function __construct(protected Application $app)
    {
        $this->temporalMocker = $this->app->make(TemporalMocker::class);
        $this->swapWorkflowClient();
    }

    protected function swapWorkflowClient(): void
    {
        /** @var FakeWorkflowClient $instance */
        $instance = $this->app->make(FakeWorkflowClient::class);

        $this->app->instance(WorkflowClientInterface::class, $instance);
    }

    public function mockWorkflows(array $workflowMocks): void
    {
        $this->initCache();

        foreach ($workflowMocks as $workflowName => $workflowResult) {
            $this->temporalMocker->mockWorkflowResult($workflowName, $workflowResult);
        }
    }

    public function mockActivities(array $activityMocks): void
    {
        $this->initCache();

        foreach ($activityMocks as $activityName => $activityResult) {
            $this->temporalMocker->mockActivityResult($activityName, $activityResult);
        }
    }

    public function assertWorkflowDispatched(string $workflowName, Closure|int|null $callback = null): void
    {
        if (is_int($callback)) {
            $this->assertWorkflowDispatchedTimes($workflowName, $callback);

            return;
        }

        PHPUnit::assertTrue(
            $this->workflowDispatched($workflowName, $callback)->count() > 0,
            "The expected [{$workflowName}] workflow was not dispatched."
        );
    }

    public function assertWorkflowDispatchedTimes(string $workflowName, int $times = 1): void
    {
        $count = $this->workflowDispatched($workflowName)->count();

        PHPUnit::assertSame(
            $times, $count,
            "The expected [{$workflowName}] workflow was dispatched {$count} times instead of {$times} times."
        );
    }

    public function assertWorkflowNotDispatched(string $workflowName, Closure|null $callback = null): void
    {
        PHPUnit::assertCount(
            0, $this->workflowDispatched($workflowName, $callback),
            "The unexpected [{$workflowName}] workflow was dispatched."
        );
    }

    protected function workflowDispatched(string $workflowName, Closure $callback = null): Collection
    {
        $callback = $callback ?: fn () => true;

        return collect($this->temporalMocker->getWorkflowDispatches($workflowName))->filter(
            fn ($arguments) => $callback(...$arguments)
        );
    }

    public function getTemporalContext(): mixed
    {
        $currentContext = Workflow::getCurrentContext();

        return match (true) {
            $currentContext instanceof Workflow\ScopedContextInterface => new FakeScopeContext($currentContext),
            $currentContext instanceof Workflow\WorkflowContextInterface => new FakeWorkflowContext($currentContext),
            default => $currentContext
        };
    }

    protected function initCache(): void
    {
        if (! $this->activityCacheCleared) {
            $this->temporalMocker->clear();
        }

        $this->activityCacheCleared = true;
    }
}
