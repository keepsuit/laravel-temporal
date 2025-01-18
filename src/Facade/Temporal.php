<?php

namespace Keepsuit\LaravelTemporal\Facade;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Keepsuit\LaravelTemporal\Builder\ActivityBuilder;
use Keepsuit\LaravelTemporal\Builder\ChildWorkflowBuilder;
use Keepsuit\LaravelTemporal\Builder\LocalActivityBuilder;
use Keepsuit\LaravelTemporal\Builder\WorkflowBuilder;
use Keepsuit\LaravelTemporal\TemporalRegistry;
use Keepsuit\LaravelTemporal\Testing\ActivityMockBuilder;
use Keepsuit\LaravelTemporal\Testing\Fakes\TemporalFake;
use Keepsuit\LaravelTemporal\Testing\TemporalTestingEnvironment;
use Keepsuit\LaravelTemporal\Testing\WorkflowMockBuilder;
use Temporal\Client\WorkflowClientInterface;
use Temporal\Worker\WorkerOptions;
use Temporal\Workflow;

/**
 * @method static WorkflowBuilder newWorkflow()
 * @method static ChildWorkflowBuilder newChildWorkflow()
 * @method static ActivityBuilder newActivity()
 * @method static LocalActivityBuilder newLocalActivity()
 * @method static void buildWorkerOptionsUsing(Closure $callback)
 * @method static WorkerOptions|null buildWorkerOptions(string $taskQueue)
 * @method static void mockWorkflows(array $workflowMocks, ?string $taskQueue = null)
 * @method static WorkflowMockBuilder mockWorkflow(string $workflowName)
 * @method static void mockActivities(array $activitiesMocks, ?string $taskQueue = null)
 * @method static ActivityMockBuilder mockActivity(string|array $activityName)
 * @method static void assertWorkflowDispatched(string $workflowName, Closure|int|null $callback = null)
 * @method static void assertWorkflowDispatchedTimes(string $workflowName, int $times = 1, Closure|null $callback = null)
 * @method static void assertWorkflowNotDispatched(string $workflowName, Closure|null $callback = null)
 * @method static void assertActivityDispatched(string|array $activityName, Closure|int|null $callback = null)
 * @method static void assertActivityDispatchedTimes(string|array $activityName, int $times = 1, Closure|null $callback = null)
 * @method static void assertActivityNotDispatched(string|array $activityName, Closure|null $callback = null)
 */
class Temporal extends Facade
{
    public static function fake(): TemporalFake
    {
        static::swap($instance = (new TemporalFake(static::$app)));

        if (! static::temporalTestingEnvironmentIsConfigured()) {
            $instance->useLocalCache();
        }

        return $instance;
    }

    protected static function temporalTestingEnvironmentIsConfigured(): bool
    {
        $temporalTestingEnvironment = $GLOBALS['_temporal_environment'] ?? null;

        return $temporalTestingEnvironment instanceof TemporalTestingEnvironment;
    }

    public static function initFakeWorker(): void
    {
        if (static::$app->environment() === 'production') {
            return;
        }

        if (! isset($_SERVER['LARAVEL_TEMPORAL']) || ! isset($_SERVER['TEMPORAL_TESTING_ENV'])) {
            throw new \RuntimeException('This method can be called only from temporal test worker');
        }

        if (isset($_SERVER['TEMPORAL_TESTING_CONFIG'])) {
            config()->set(\Safe\json_decode((string) $_SERVER['TEMPORAL_TESTING_CONFIG'], true) ?? []);
            DB::purge();
        }

        if (isset($_SERVER['TEMPORAL_TESTING_REGISTRY'])) {
            $registryState = \Safe\json_decode((string) $_SERVER['TEMPORAL_TESTING_REGISTRY'], true) ?? [];

            static::$app->bind(TemporalRegistry::class, fn () => (new TemporalRegistry)
                ->registerWorkflows(...Arr::get($registryState, 'workflows', []))
                ->registerActivities(...Arr::get($registryState, 'activities', []))
            );
        }

        static::swap((new TemporalFake(static::$app)));
    }

    public static function getTemporalContext(): Workflow\ScopedContextInterface
    {
        $instance = static::getFacadeRoot();

        if (is_object($instance) && method_exists($instance, 'getTemporalContext')) {
            return $instance->getTemporalContext();
        }

        return Workflow::getCurrentContext();
    }

    protected static function getFacadeAccessor(): string
    {
        return \Keepsuit\LaravelTemporal\Contracts\Temporal::class;
    }

    public static function registry(): TemporalRegistry
    {
        return static::$app->make(TemporalRegistry::class);
    }

    public static function workflowClient(): WorkflowClientInterface
    {
        return static::$app->make(WorkflowClientInterface::class);
    }
}
