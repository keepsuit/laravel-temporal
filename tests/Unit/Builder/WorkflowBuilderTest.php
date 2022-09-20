<?php

use Keepsuit\LaravelTemporal\Builder\WorkflowBuilder;
use Keepsuit\LaravelTemporal\DataConverter\LaravelPayloadConverter;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\DemoWorkflow;
use Temporal\Client\WorkflowClient;
use Temporal\Client\WorkflowClientInterface;
use Temporal\Internal\Client\WorkflowProxy;

it('can build client with default options', function () {
    $client = app(WorkflowClientInterface::class);

    expect($client)
        ->toBeInstanceOf(WorkflowClient::class);

    expect(invade(invade($client)->converter)->converters['json/plain'])
        ->toBeInstanceOf(LaravelPayloadConverter::class);
});

it('can build workflow with default options', function () {
    config()->set('temporal.queue', 'test-queue');
    config()->set('temporal.namespace', 'test-namespace');

    $workflow = WorkflowBuilder::new()
        ->build(DemoWorkflow::class);

    expect($workflow)
        ->toBeInstanceOf(WorkflowProxy::class);

    /** @var \Temporal\Client\WorkflowOptions $workflowOptions */
    $workflowOptions = invade(invade($workflow)->stub)->options;

    expect($workflowOptions)
        ->taskQueue->toBe('test-queue');

    /** @var \Temporal\Client\ClientOptions $clientOptions */
    $clientOptions = invade(invade($workflow)->client)->clientOptions;

    expect($clientOptions)
        ->namespace->toBe('test-namespace');
});

it('can build workflow with custom options', function () {
    config()->set('temporal.queue', 'test-queue');

    $workflow = WorkflowBuilder::new()
        ->withTaskQueue('custom-queue')
        ->build(DemoWorkflow::class);

    expect($workflow)
        ->toBeInstanceOf(WorkflowProxy::class);

    /** @var \Temporal\Client\WorkflowOptions $workflowOptions */
    $workflowOptions = invade(invade($workflow)->stub)->options;

    expect($workflowOptions)
        ->taskQueue->toBe('custom-queue');
});
