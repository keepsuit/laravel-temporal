<?php

namespace Keepsuit\LaravelTemporal\Facade;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Keepsuit\LaravelTemporal\Builder\ActivityBuilder;
use Keepsuit\LaravelTemporal\Builder\ChildWorkflowBuilder;
use Keepsuit\LaravelTemporal\Builder\LocalActivityBuilder;
use Keepsuit\LaravelTemporal\Builder\WorkflowBuilder;
use Keepsuit\LaravelTemporal\Testing\ActivityMockBuilder;
use Keepsuit\LaravelTemporal\Testing\Fakes\TemporalFake;
use Keepsuit\LaravelTemporal\Testing\WorkflowMockBuilder;
use Temporal\Workflow;

/**
 * @method static WorkflowBuilder newWorkflow()
 * @method static ChildWorkflowBuilder newChildWorkflow()
 * @method static ActivityBuilder newActivity()
 * @method static LocalActivityBuilder newLocalActivity()
 * @method static void mockWorkflows(array $workflowMocks, ?string $taskQueue = null)
 * @method static WorkflowMockBuilder mockWorkflow(string $workflowName)
 * @method static void mockActivities(array $activitiesMocks, ?string $taskQueue = null)
 * @method static ActivityMockBuilder mockActivity(string|array $activityName)
 * @method static void assertWorkflowDispatched(string $workflowName, \Closure|int|null $callback = null)
 * @method static void assertWorkflowDispatchedTimes(string $workflowName, int $times = 1, \Closure|null $callback = null)
 * @method static void assertWorkflowNotDispatched(string $workflowName, \Closure|null $callback = null)
 * @method static void assertActivityDispatched(string|array $activityName, \Closure|int|null $callback = null)
 * @method static void assertActivityDispatchedTimes(string|array $activityName, int $times = 1, \Closure|null $callback = null)
 * @method static void assertActivityNotDispatched(string|array $activityName, \Closure|null $callback = null)
 */
class Temporal extends Facade
{
    public static function fake(): TemporalFake
    {
        static::swap($instance = (new TemporalFake(static::$app)));

        $instance->init();

        return $instance;
    }

    public static function initFakeWorker(): void
    {
        if (static::$app->environment() === 'production') {
            return;
        }

        if (! env('LARAVEL_TEMPORAL') || ! env('TEMPORAL_TESTING_ENV')) {
            throw new \RuntimeException('This method can be called only from temporal test worker');
        }

        if (env('TEMPORAL_TESTING_CONFIG') !== null) {
            config()->set(json_decode(env('TEMPORAL_TESTING_CONFIG'), true) ?? []);
            DB::purge();
        }

        static::swap((new TemporalFake(static::$app)));
    }

    /**
     * @return Workflow\WorkflowContextInterface|Workflow\ScopedContextInterface|object
     */
    public static function getTemporalContext(): mixed
    {
        $instance = static::getFacadeRoot();

        if (method_exists($instance, 'getTemporalContext')) {
            return $instance->getTemporalContext();
        }

        return Workflow::getCurrentContext();
    }

    protected static function getFacadeAccessor(): string
    {
        return \Keepsuit\LaravelTemporal\Temporal::class;
    }
}
