<?php

use Keepsuit\LaravelTemporal\Support\DiscoverWorkflows;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\ActivityOptionsWorkflow;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\DemoWorkflow;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\DemoWorkflowInterfaceOnly;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\DemoWorkflowWithoutInterface;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\InterceptorWorkflow;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\TesterWorkflow;

it('can discovery workflows', function () {
    $workflows = DiscoverWorkflows::within(
        __DIR__.'/../../Fixtures/WorkflowDiscovery/Workflows',
    );

    expect($workflows)->toBe([
        ActivityOptionsWorkflow::class,
        DemoWorkflow::class,
        DemoWorkflowInterfaceOnly::class,
        DemoWorkflowWithoutInterface::class,
        InterceptorWorkflow::class,
        TesterWorkflow::class,
    ]);
});
