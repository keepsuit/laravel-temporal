<?php

use Keepsuit\LaravelTemporal\Support\DiscoverWorkflows;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\DemoWorkflow;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\DemoWorkflowInterface;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\DemoWorkflowInterfaceOnly;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\DemoWorkflowWithoutInterface;

it('can discovery workflows', function () {
    class_alias(DemoWorkflow::class, 'Tests\Fixtures\WorkflowDiscovery\Workflows\DemoWorkflow');
    class_alias(DemoWorkflowInterface::class, 'Tests\Fixtures\WorkflowDiscovery\Workflows\DemoWorkflowInterface');
    class_alias(DemoWorkflowWithoutInterface::class, 'Tests\Fixtures\WorkflowDiscovery\Workflows\DemoWorkflowWithoutInterface');
    class_alias(DemoWorkflowInterfaceOnly::class, 'Tests\Fixtures\WorkflowDiscovery\Workflows\DemoWorkflowInterfaceOnly');

    $workflows = DiscoverWorkflows::within(
        __DIR__.'/../../Fixtures/WorkflowDiscovery/Workflows',
        getcwd()
    );

    expect($workflows)->toBe([
        DemoWorkflow::class,
        DemoWorkflowInterfaceOnly::class,
        DemoWorkflowWithoutInterface::class,
    ]);
});
