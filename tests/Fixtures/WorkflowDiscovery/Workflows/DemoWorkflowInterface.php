<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows;

use Temporal\Workflow\WorkflowInterface;
use Temporal\Workflow\WorkflowMethod;

#[WorkflowInterface]
interface DemoWorkflowInterface
{
    #[WorkflowMethod(name: 'demo.greet')]
    public function greet(string $name): \Generator;
}
