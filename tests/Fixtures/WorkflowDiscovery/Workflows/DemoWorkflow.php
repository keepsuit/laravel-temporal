<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows;

use Carbon\CarbonInterval;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivityInterface;
use Temporal\Activity\LocalActivityOptions;
use Temporal\Workflow;

class DemoWorkflow implements DemoWorkflowInterface
{
    /**
     * @var mixed|DemoActivityInterface
     */
    protected mixed $activity;

    public function __construct()
    {
        $this->activity = Workflow::newActivityStub(
            DemoActivityInterface::class,
            LocalActivityOptions::new()
                ->withStartToCloseTimeout(CarbonInterval::seconds(1))
        );
    }

    public function greet(string $name): \Generator
    {
        return yield $this->activity->greet($name);
    }
}
