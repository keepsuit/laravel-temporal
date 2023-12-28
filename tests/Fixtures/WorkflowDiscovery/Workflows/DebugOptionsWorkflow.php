<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows;

use Carbon\CarbonInterval;
use Keepsuit\LaravelTemporal\Facade\Temporal;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivityInterface;
use Temporal\Activity\ActivityOptions;
use Temporal\Workflow;
use Temporal\Workflow\WorkflowInterface;
use Temporal\Workflow\WorkflowMethod;

#[WorkflowInterface]
class DebugOptionsWorkflow
{
    #[WorkflowMethod(name: 'debug.activityOptions')]
    #[Workflow\ReturnType('array')]
    public function activityOptions(): \Generator
    {
        $activity = Temporal::newActivity()
            ->withStartToCloseTimeout(CarbonInterval::seconds(1))
            ->build(DemoActivityInterface::class);

        $result = yield $activity->greet('John');

        $options = invade($activity)->options;
        assert($options instanceof ActivityOptions);

        return [
            'taskQueue' => $options->taskQueue,
            'scheduleToCloseTimeout' => $options->scheduleToCloseTimeout,
            'scheduleToStartTimeout' => $options->scheduleToStartTimeout,
            'startToCloseTimeout' => $options->startToCloseTimeout,
        ];
    }
}
