<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows;

use Temporal\Workflow;
use Temporal\Workflow\WorkflowInterface;
use Temporal\Workflow\WorkflowMethod;

#[WorkflowInterface]
class AsyncWorkflow
{
    #[WorkflowMethod(name: 'async')]
    #[Workflow\ReturnType('string')]
    public function async(): \Generator
    {
        yield Workflow::timer(3);

        return 'ok';
    }
}
