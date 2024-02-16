<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows;

use Carbon\CarbonInterval;
use Keepsuit\LaravelTemporal\Facade\Temporal;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Interceptors\InterceptorSingleton;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivity;
use Temporal\Internal\Workflow\ActivityProxy;
use Temporal\Workflow;
use Temporal\Workflow\WorkflowInterface;
use Temporal\Workflow\WorkflowMethod;

#[WorkflowInterface]
class InterceptorWorkflow
{
    /**
     * @var ActivityProxy<DemoActivity>
     */
    protected ActivityProxy $activity;

    public function __construct()
    {
        $this->activity = Temporal::newActivity()
            ->withStartToCloseTimeout(CarbonInterval::seconds(1))
            ->build(DemoActivity::class);
    }

    #[WorkflowMethod(name: 'interceptors')]
    #[Workflow\ReturnType('array')]
    public function interceptors(): \Generator
    {
        yield $this->activity->greet('John');

        return InterceptorSingleton::getInstance()->all();
    }
}
