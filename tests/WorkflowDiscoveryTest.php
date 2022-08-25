<?php

use Keepsuit\LaravelTemporal\Support\DiscoverWorkflows;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\DemoWorkflow;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\DemoWorkflowInterface;

it('can discovery workflows', function () {
    class_alias(DemoWorkflow::class, 'Tests\Fixtures\WorkflowDiscovery\Workflows\DemoWorkflow');
    class_alias(DemoWorkflowInterface::class, 'Tests\Fixtures\WorkflowDiscovery\Workflows\DemoWorkflowInterface');

    $workflows = DiscoverWorkflows::within(
        __DIR__.'/Fixtures/WorkflowDiscovery/Workflows',
        getcwd()
    );

    expect($workflows)->toBe([
        DemoWorkflow::class,
    ]);
});
