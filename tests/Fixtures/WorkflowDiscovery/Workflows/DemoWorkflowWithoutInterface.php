<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows;

use Carbon\CarbonInterval;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivity;
use Temporal\Activity\ActivityOptions;
use Temporal\Workflow;
use Temporal\Workflow\WorkflowInterface;
use Temporal\Workflow\WorkflowMethod;

#[WorkflowInterface]
class DemoWorkflowWithoutInterface
{
    #[WorkflowMethod(name: 'demo_without_interface.greet')]
    public function greet(string $name): \Generator
    {
        $activity = Workflow::newActivityStub(
            DemoActivity::class,
            ActivityOptions::new()->withStartToCloseTimeout(CarbonInterval::seconds(1))
        );

        return yield $activity->greet($name);
    }
}
