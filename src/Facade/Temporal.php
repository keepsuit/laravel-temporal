<?php

namespace Keepsuit\LaravelTemporal\Facade;

use Illuminate\Support\Facades\Facade;
use Keepsuit\LaravelTemporal\Builder\ActivityBuilder;
use Keepsuit\LaravelTemporal\Builder\ChildWorkflowBuilder;
use Keepsuit\LaravelTemporal\Builder\LocalActivityBuilder;
use Keepsuit\LaravelTemporal\Builder\WorkflowBuilder;
use Keepsuit\LaravelTemporal\Testing\Fakes\TemporalFake;
use Temporal\Workflow;

/**
 * @method static WorkflowBuilder newWorkflow()
 * @method static ChildWorkflowBuilder newChildWorkflow()
 * @method static ActivityBuilder newActivity()
 * @method static LocalActivityBuilder newLocalActivity()
 * @method static void mockWorkflows(array $workflowMocks)
 * @method static void mockActivities(array $activitiesMocks)
 * @method static void assertWorkflowDispatched(string $workflowName, \Closure|int|null $callback = null)
 * @method static void assertWorkflowDispatchedTimes(string $workflowName, int $times = 1)
 * @method static void assertWorkflowNotDispatched(string $workflowName, \Closure|null $callback = null)
 */
class Temporal extends Facade
{
    public static function fake(): TemporalFake
    {
        static::swap($instance = new TemporalFake(static::$app));

        return $instance;
    }

    /**
     * @return Workflow\WorkflowContextInterface|Workflow\ScopedContextInterface|object
     */
    public static function getTemporalContext(): object
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
