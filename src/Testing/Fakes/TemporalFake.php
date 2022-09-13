<?php

namespace Keepsuit\LaravelTemporal\Testing\Fakes;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Keepsuit\LaravelTemporal\Temporal;
use Keepsuit\LaravelTemporal\Testing\TemporalMocker;
use PHPUnit\Framework\Assert as PHPUnit;
use Spiral\Attributes\AttributeReader;
use Temporal\Activity\ActivityInterface;
use Temporal\Activity\LocalActivityInterface;
use Temporal\Client\WorkflowClientInterface;
use Temporal\Internal\Declaration\Prototype\ActivityPrototype;
use Temporal\Internal\Declaration\Reader\ActivityReader;
use Temporal\Internal\Declaration\Reader\WorkflowReader;
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
        foreach ($workflowMocks as $workflowName => $workflowResult) {
            $this->temporalMocker->mockWorkflowResult($this->normalizeWorkflowName($workflowName), $workflowResult);
        }
    }

    public function mockActivities(array $activityMocks): void
    {
        $activityMocks = $this->normalizeActivityMocks($activityMocks);

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

        return collect($this->temporalMocker->getWorkflowDispatches($this->normalizeWorkflowName($workflowName)))->filter(
            fn ($arguments) => $callback(...$arguments)
        );
    }

    public function assertActivityDispatched(string|array $activityName, Closure|int|null $callback = null): void
    {
        $activityName = $this->normalizeActivityName($activityName);

        if (is_int($callback)) {
            $this->assertActivityDispatchedTimes($activityName, $callback);

            return;
        }

        PHPUnit::assertTrue(
            $this->activityDispatched($activityName, $callback)->count() > 0,
            "The expected [{$activityName}] activity was not dispatched."
        );
    }

    public function assertActivityDispatchedTimes(string|array $activityName, int $times = 1): void
    {
        $activityName = $this->normalizeActivityName($activityName);

        $count = $this->activityDispatched($activityName)->count();

        PHPUnit::assertSame(
            $times, $count,
            "The expected [{$activityName}] activity was dispatched {$count} times instead of {$times} times."
        );
    }

    public function assertActivityNotDispatched(string|array $activityName, Closure|null $callback = null): void
    {
        $activityName = $this->normalizeActivityName($activityName);

        PHPUnit::assertCount(
            0, $this->activityDispatched($activityName, $callback),
            "The unexpected [{$activityName}] activity was dispatched."
        );
    }

    protected function activityDispatched(string|array $activityName, Closure $callback = null): Collection
    {
        $activityName = $this->normalizeActivityName($activityName);

        $callback = $callback ?: fn () => true;

        return collect($this->temporalMocker->getActivityDispatches($activityName))->filter(
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

    public function init(): void
    {
        $this->initCache();
    }

    protected function normalizeWorkflowName(string $workflowName): string
    {
        if (! interface_exists($workflowName)) {
            return $workflowName;
        }

        try {
            return (new WorkflowReader(new AttributeReader()))->fromClass($workflowName)->getID();
        } catch (\Exception) {
            return $workflowName;
        }
    }

    protected function normalizeActivityMocks(array $activityMocks): array
    {
        return Collection::make($activityMocks)
            ->mapWithKeys(function (mixed $mocks, string $activity) {
                if (interface_exists($activity) && is_array($mocks)) {
                    return Collection::make($mocks)
                        ->mapWithKeys(function (mixed $value, string $method) use ($activity) {
                            $activityName = $this->normalizeActivityName([$activity, $method]);

                            return $activityName ? [$activityName => $value] : [];
                        })
                        ->all();
                }

                return [$activity => $mocks];
            })
            ->all();
    }

    protected function normalizeActivityName(string|array $activityName): ?string
    {
        if (is_string($activityName)) {
            return $activityName;
        }

        if (count($activityName) !== 2) {
            return null;
        }

        if (! interface_exists($activityName[0])) {
            return null;
        }

        try {
            /** @var ActivityPrototype[] $activities */
            $activities = (new ActivityReader(new AttributeReader()))->fromClass($activityName[0]);

            if ($activities === []) {
                return null;
            }

            $prefix = $activities[0]->getClass()
                ->getAttributes($activities[0]->isLocalActivity() ? LocalActivityInterface::class : ActivityInterface::class)[0]
                ->getArguments()['prefix'] ?? '';

            /** @var Collection $activityMap */
            $activityMap = Collection::make($activities)
                ->mapWithKeys(fn (ActivityPrototype $prototype) => [
                    $prototype->getID() => $prototype->getID(),
                    Str::after($prototype->getID(), $prefix) => $prototype->getID(),
                    $prototype->getHandler()->getName() => $prototype->getID(),
                ]);

            return $activityMap->get($activityName[1]);
        } catch (\Exception) {
            return null;
        }
    }
}
