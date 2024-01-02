<?php

use Carbon\CarbonInterval;
use Keepsuit\LaravelTemporal\Builder\WorkflowBuilder;
use Keepsuit\LaravelTemporal\DataConverter\LaravelPayloadConverter;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\DemoWorkflow;
use Temporal\Client\ClientOptions;
use Temporal\Client\WorkflowClient;
use Temporal\Client\WorkflowClientInterface;
use Temporal\Common\RetryOptions;
use Temporal\Internal\Client\WorkflowProxy;
use Temporal\Internal\Client\WorkflowStub;
use Temporal\WorkerFactory;

it('can build client with default options', function () {
    $client = app(WorkflowClientInterface::class);

    expect($client)
        ->toBeInstanceOf(WorkflowClient::class);

    expect(invade(invade($client)->converter)->converters['json/plain'])
        ->toBeInstanceOf(LaravelPayloadConverter::class);
});

it('can build workflow with default config options', function (bool $typed) {
    $builder = WorkflowBuilder::new();

    expect($builder)
        ->taskQueue->toBe(WorkerFactory::DEFAULT_TASK_QUEUE)
        ->retryOptions->not->toBeNull()
        ->retryOptions->initialInterval->totalSeconds->toBe(RetryOptions::DEFAULT_INITIAL_INTERVAL)
        ->retryOptions->backoffCoefficient->toBe(RetryOptions::DEFAULT_BACKOFF_COEFFICIENT)
        ->retryOptions->maximumInterval->totalSeconds->toBe(RetryOptions::DEFAULT_MAXIMUM_INTERVAL)
        ->retryOptions->maximumAttempts->toBe(RetryOptions::DEFAULT_MAXIMUM_ATTEMPTS);

    $workflow = match ($typed) {
        true => $builder->build(DemoWorkflow::class),
        false => $builder->buildUntyped('demo.greet'),
    };

    if ($typed) {
        expect($workflow)
            ->toBeInstanceOf(WorkflowProxy::class);
    } else {
        expect($workflow)
            ->toBeInstanceOf(WorkflowStub::class);
    }

    $stub = match ($typed) {
        true => invade($workflow)->stub,
        false => $workflow,
    };
    assert($stub instanceof WorkflowStub);

    expect($stub->getOptions())
        ->taskQueue->toBe(WorkerFactory::DEFAULT_TASK_QUEUE)
        ->retryOptions->not->toBeNull()
        ->retryOptions->initialInterval->totalSeconds->toBe(RetryOptions::DEFAULT_INITIAL_INTERVAL)
        ->retryOptions->backoffCoefficient->toBe(RetryOptions::DEFAULT_BACKOFF_COEFFICIENT)
        ->retryOptions->maximumInterval->totalSeconds->toBe(RetryOptions::DEFAULT_MAXIMUM_INTERVAL)
        ->retryOptions->maximumAttempts->toBe(RetryOptions::DEFAULT_MAXIMUM_ATTEMPTS);

    /** @var ClientOptions $clientOptions */
    $clientOptions = invade($stub)->clientOptions;

    expect($clientOptions)
        ->namespace->toBe(ClientOptions::DEFAULT_NAMESPACE);
})->with([
    'typed' => true,
    'untyped' => false,
]);

it('can build workflow with custom config options', function (bool $typed) {
    config()->set('temporal.queue', 'test-queue');
    config()->set('temporal.namespace', 'test-namespace');
    config()->set('temporal.retry.workflow', [
        'initial_interval' => 5,
        'backoff_coefficient' => 6.0,
        'maximum_interval' => 500,
        'maximum_attempts' => 10,
    ]);

    $builder = WorkflowBuilder::new();

    expect($builder)
        ->taskQueue->toBe('test-queue')
        ->retryOptions->not->toBeNull()
        ->retryOptions->initialInterval->totalSeconds->toBe(5)
        ->retryOptions->backoffCoefficient->toBe(6.0)
        ->retryOptions->maximumInterval->totalSeconds->toBe(500)
        ->retryOptions->maximumAttempts->toBe(10);

    $workflow = match ($typed) {
        true => $builder->build(DemoWorkflow::class),
        false => $builder->buildUntyped('demo.greet'),
    };

    $stub = match ($typed) {
        true => invade($workflow)->stub,
        false => $workflow,
    };
    assert($stub instanceof WorkflowStub);

    expect($stub->getOptions())
        ->taskQueue->toBe('test-queue')
        ->retryOptions->not->toBeNull()
        ->retryOptions->initialInterval->totalSeconds->toBe(5)
        ->retryOptions->backoffCoefficient->toBe(6.0)
        ->retryOptions->maximumInterval->totalSeconds->toBe(500)
        ->retryOptions->maximumAttempts->toBe(10);

    /** @var ClientOptions $clientOptions */
    $clientOptions = invade($stub)->clientOptions;

    expect($clientOptions)
        ->namespace->toBe('test-namespace');
})->with([
    'typed' => true,
    'untyped' => false,
]);

it('can build workflow with custom options', function (bool $typed) {
    $builder = WorkflowBuilder::new()
        ->withTaskQueue('custom-queue')
        ->withRetryOptions(RetryOptions::new()->withMaximumAttempts(5))
        ->withWorkflowExecutionTimeout(CarbonInterval::seconds(10));

    expect($builder)
        ->taskQueue->toBe('custom-queue')
        ->retryOptions->maximumAttempts->toBe(5)
        ->workflowExecutionTimeout->totalSeconds->toBe(10);

    $workflow = match ($typed) {
        true => $builder->build(DemoWorkflow::class),
        false => $builder->buildUntyped('demo.greet'),
    };

    $stub = match ($typed) {
        true => invade($workflow)->stub,
        false => $workflow,
    };
    assert($stub instanceof WorkflowStub);

    expect($stub->getOptions())
        ->taskQueue->toBe('custom-queue')
        ->retryOptions->maximumAttempts->toBe(5)
        ->workflowExecutionTimeout->totalSeconds->toBe(10);
})->with([
    'typed' => true,
    'untyped' => false,
]);

it('can build running workflow', function (bool $typed) {
    $builder = WorkflowBuilder::new()
        ->withRunId('test-run-id');

    expect($builder)
        ->runId->toBe('test-run-id');

    $workflow = match ($typed) {
        true => $builder->build(DemoWorkflow::class),
        false => $builder->buildUntyped('demo.greet'),
    };

    $stub = match ($typed) {
        true => invade($workflow)->stub,
        false => $workflow,
    };
    assert($stub instanceof WorkflowStub);

    expect($stub->getExecution())
        ->getRunID()->toBe('test-run-id');
})->with([
    'typed' => true,
    'untyped' => false,
]);
