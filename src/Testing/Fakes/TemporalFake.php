<?php

namespace Keepsuit\LaravelTemporal\Testing\Fakes;

use Closure;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Keepsuit\LaravelTemporal\Temporal;
use Keepsuit\LaravelTemporal\Testing\ActivityMocker;
use PHPUnit\Framework\Assert as PHPUnit;
use Temporal\Client\WorkflowClientInterface;
use Throwable;

class TemporalFake extends Temporal
{
    protected FakeWorkflowClient $workflowClient;

    protected ActivityMocker $activityMocker;

    /**
     * @var array<string,mixed[]>
     */
    protected array $dispatchedWorkflows = [];

    public function __construct(protected Application $app)
    {
        $this->workflowClient = $this->swapWorkflowClient();
        $this->activityMocker = $this->app->make(ActivityMocker::class);

        $this->activityMocker->clear();
    }

    protected function swapWorkflowClient(): FakeWorkflowClient
    {
        /** @var FakeWorkflowClient $instance */
        $instance = $this->app->make(FakeWorkflowClient::class);

        $instance->onWorkflowStart(function (string $workflowName, ...$args) {
            $this->dispatchedWorkflows[$workflowName][] = $args;
        });

        $this->app->instance(WorkflowClientInterface::class, $instance);

        return $instance;
    }

    public function mockWorkflows(array $workflowMocks): void
    {
        foreach ($workflowMocks as $workflowName => $workflowResult) {
            $this->workflowClient->mockWorkflow($workflowName, $workflowResult);
        }
    }

    public function mockActivities(array $activityMocks): void
    {
        foreach ($activityMocks as $activityName => $activityResult) {
            if ($activityResult instanceof Closure || is_callable($activityResult)) {
                try {
                    $this->activityMocker->expectCompletion($activityName, $activityResult());
                } catch (Exception $exception) {
                    $this->activityMocker->expectFailure($activityName, $exception);
                }

                continue;
            }

            if ($activityResult instanceof Throwable) {
                $this->activityMocker->expectFailure($activityName, $activityResult);

                continue;
            }

            $this->activityMocker->expectCompletion($activityName, $activityResult);
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
        if (! $this->hasDispatchedWorkflow($workflowName)) {
            return Collection::make();
        }

        $callback = $callback ?: fn () => true;

        return collect($this->dispatchedWorkflows[$workflowName])->filter(
            fn ($arguments) => $callback(...$arguments)
        );
    }

    protected function hasDispatchedWorkflow(string $workflowName): bool
    {
        return isset($this->dispatchedWorkflows[$workflowName]) && ! empty($this->dispatchedWorkflows[$workflowName]);
    }
}
