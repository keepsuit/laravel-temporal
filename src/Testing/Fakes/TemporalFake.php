<?php

namespace Keepsuit\LaravelTemporal\Testing\Fakes;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Keepsuit\LaravelTemporal\Temporal;
use Keepsuit\LaravelTemporal\Testing\ActivityMockBuilder;
use Keepsuit\LaravelTemporal\Testing\TemporalMocker;
use Keepsuit\LaravelTemporal\Testing\WorkflowMockBuilder;
use PHPUnit\Framework\Assert as PHPUnit;
use Spiral\Attributes\AttributeReader;
use Temporal\Activity\ActivityInterface;
use Temporal\Activity\LocalActivityInterface;
use Temporal\Client\ClientOptions;
use Temporal\Client\GRPC\ServiceClientInterface;
use Temporal\Client\WorkflowClientInterface;
use Temporal\DataConverter\DataConverterInterface;
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
        $this->app->instance(WorkflowClientInterface::class, new FakeWorkflowClient(
            serviceClient: $this->app->make(ServiceClientInterface::class),
            options: (new ClientOptions())->withNamespace(config('temporal.namespace')),
            converter: $this->app->make(DataConverterInterface::class),
        ));
    }

    public function mockWorkflows(array $workflowMocks, ?string $taskQueue = null): void
    {
        $this->initCache();

        foreach ($workflowMocks as $workflowName => $workflowResult) {
            if (is_int($workflowName) && is_string($workflowResult)) {
                $workflowName = $workflowResult;
                $workflowResult = null;
            }

            $this->temporalMocker->mockWorkflowResult($this->normalizeWorkflowName($workflowName), $workflowResult, $taskQueue);
        }
    }

    public function mockWorkflow(string $workflowName): WorkflowMockBuilder
    {
        $this->initCache();

        return new WorkflowMockBuilder($this->normalizeWorkflowName($workflowName));
    }

    public function mockActivities(array $activityMocks, ?string $taskQueue = null): void
    {
        $this->initCache();

        $activityMocks = $this->normalizeActivityMocks($activityMocks);

        foreach ($activityMocks as $activityName => $activityResult) {
            if (is_int($activityName) && is_string($activityResult)) {
                $activityName = $activityResult;
                $activityResult = null;
            }

            $this->temporalMocker->mockActivityResult($activityName, $activityResult, $taskQueue);
        }
    }

    public function mockActivity(string|array $activityName): ActivityMockBuilder
    {
        $this->initCache();

        return new ActivityMockBuilder($this->normalizeActivityName($activityName));
    }

    public function assertWorkflowDispatched(string $workflowName, Closure|int|null $callback = null): void
    {
        if (is_int($callback)) {
            $this->assertWorkflowDispatchedTimes($workflowName, $callback);

            return;
        }

        PHPUnit::assertTrue(
            $this->workflowDispatched($workflowName, $callback)->count() > 0,
            sprintf('The expected [%s] workflow was not dispatched.', $workflowName)
        );
    }

    public function assertWorkflowDispatchedTimes(string $workflowName, int $times = 1, ?Closure $callback = null): void
    {
        $count = $this->workflowDispatched($workflowName, $callback)->count();

        PHPUnit::assertSame(
            $times, $count,
            sprintf('The expected [%s] workflow was dispatched %d times instead of %d times.', $workflowName, $count, $times)
        );
    }

    public function assertWorkflowNotDispatched(string $workflowName, ?Closure $callback = null): void
    {
        PHPUnit::assertCount(
            0, $this->workflowDispatched($workflowName, $callback),
            sprintf('The unexpected [%s] workflow was dispatched.', $workflowName)
        );
    }

    protected function workflowDispatched(string $workflowName, ?Closure $callback = null): Collection
    {
        $callback = $callback ?: fn () => true;

        return collect($this->temporalMocker->getWorkflowDispatches($this->normalizeWorkflowName($workflowName)))->filter(
            fn (array $data) => $callback(...[...$data['args'], $data['taskQueue']])
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
            sprintf('The expected [%s] activity was not dispatched.', $activityName)
        );
    }

    public function assertActivityDispatchedTimes(string|array $activityName, int $times = 1, ?Closure $callback = null): void
    {
        $activityName = $this->normalizeActivityName($activityName);

        $count = $this->activityDispatched($activityName, $callback)->count();

        PHPUnit::assertSame(
            $times, $count,
            sprintf('The expected [%s] activity was dispatched %d times instead of %d times.', $activityName, $count, $times)
        );
    }

    public function assertActivityNotDispatched(string|array $activityName, ?Closure $callback = null): void
    {
        $activityName = $this->normalizeActivityName($activityName);

        PHPUnit::assertCount(
            0, $this->activityDispatched($activityName, $callback),
            sprintf('The unexpected [%s] activity was dispatched.', $activityName)
        );
    }

    protected function activityDispatched(string|array $activityName, ?Closure $callback = null): Collection
    {
        $activityName = $this->normalizeActivityName($activityName);

        $callback = $callback ?: fn () => true;

        return collect($this->temporalMocker->getActivityDispatches($activityName))->filter(
            fn (array $data) => $callback(...[...$data['args'], $data['taskQueue']])
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

    public function useLocalCache(): TemporalFake
    {
        $this->temporalMocker->localOnly();

        return $this;
    }

    protected function normalizeWorkflowName(string $workflowName): string
    {
        if (! interface_exists($workflowName) && ! class_exists($workflowName)) {
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
            ->mapWithKeys(function (mixed $mocks, string $activity): array {
                if (interface_exists($activity) && is_array($mocks)) {
                    return Collection::make($mocks)
                        ->mapWithKeys(function (mixed $value, string $method) use ($activity): array {
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

        if (! interface_exists($activityName[0]) && ! class_exists($activityName[0])) {
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
