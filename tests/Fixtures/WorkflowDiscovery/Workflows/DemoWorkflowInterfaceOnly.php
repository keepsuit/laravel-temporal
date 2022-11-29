<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows;

use Temporal\Workflow\WorkflowInterface;
use Temporal\Workflow\WorkflowMethod;

#[WorkflowInterface]
interface DemoWorkflowInterfaceOnly
{
    #[WorkflowMethod(name: 'demo_interface_only.greet')]
    public function greet(string $name): \Generator;
}
