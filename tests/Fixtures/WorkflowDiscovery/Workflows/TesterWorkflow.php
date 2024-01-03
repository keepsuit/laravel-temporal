<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows;

use Carbon\CarbonInterval;
use Keepsuit\LaravelTemporal\Facade\Temporal;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivity;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Activities\DemoLocalActivity;
use Temporal\Internal\Workflow\ActivityProxy;
use Temporal\Internal\Workflow\ChildWorkflowProxy;
use Temporal\Workflow;
use Temporal\Workflow\WorkflowInterface;
use Temporal\Workflow\WorkflowMethod;

#[WorkflowInterface]
class TesterWorkflow
{
    /**
     * @var ActivityProxy<DemoActivity>
     */
    protected ActivityProxy $activity;

    /**
     * @var ActivityProxy<DemoLocalActivity>
     */
    protected ActivityProxy $localActivity;

    /**
     * @var ChildWorkflowProxy<DemoWorkflow>
     */
    protected ChildWorkflowProxy $childWorkflow;

    public function __construct()
    {
        $this->activity = Temporal::newActivity()
            ->withStartToCloseTimeout(CarbonInterval::seconds(1))
            ->build(DemoActivity::class);

        $this->localActivity = Temporal::newLocalActivity()
            ->withStartToCloseTimeout(CarbonInterval::seconds(1))
            ->build(DemoLocalActivity::class);

        $this->childWorkflow = Temporal::newChildWorkflow()
            ->withWorkflowRunTimeout(CarbonInterval::seconds(1))
            ->build(DemoWorkflow::class);
    }

    #[WorkflowMethod(name: 'tester')]
    #[Workflow\ReturnType('array')]
    public function tester(string $target): \Generator
    {
        return match ($target) {
            'activity' => ['activity' => yield $this->activity->greet('John')],
            'local_activity' => ['local_activity' => yield $this->localActivity->greet('John')],
            'child' => ['child' => sprintf('(child) %s', yield $this->childWorkflow->greet('John'))],
            default => [
                'activity' => yield $this->activity->greet('John'),
                'local_activity' => yield $this->localActivity->greet('John'),
                'child' => sprintf('(child) %s', yield $this->childWorkflow->greet('John')),
            ]
        };
    }
}
