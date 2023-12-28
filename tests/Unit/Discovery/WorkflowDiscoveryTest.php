<?php

use Keepsuit\LaravelTemporal\Support\DiscoverWorkflows;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\DebugOptionsWorkflow;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\DemoWorkflow;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\DemoWorkflowInterface;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\DemoWorkflowInterfaceOnly;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\DemoWorkflowWithoutInterface;

it('can discovery workflows', function () {
    $workflows = DiscoverWorkflows::within(
        __DIR__.'/../../Fixtures/WorkflowDiscovery/Workflows',
    );

    expect($workflows)->toBe([
        DebugOptionsWorkflow::class,
        DemoWorkflow::class,
        DemoWorkflowInterfaceOnly::class,
        DemoWorkflowWithoutInterface::class,
    ]);
});
