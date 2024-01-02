<?php

use Carbon\CarbonInterval;
use Keepsuit\LaravelTemporal\Facade\Temporal;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Workflows\DemoWorkflowInterface;
use Temporal\Client\ClientOptions;
use Temporal\Common\RetryOptions;
use Temporal\WorkerFactory;
use Temporal\Workflow\ChildWorkflowOptions;

beforeEach(function () {
    Temporal::fake();
});

it('can build child workflow with default config options', function (bool $typed) {
    $contextMock = mockTemporalContext(validateWorkflowOptions: function (ChildWorkflowOptions $workflowOptions): bool {
        expect($workflowOptions)
            ->namespace->toBe(ClientOptions::DEFAULT_NAMESPACE)
            ->taskQueue->toBe(WorkerFactory::DEFAULT_TASK_QUEUE)
            ->retryOptions->not->toBeNull()
            ->retryOptions->initialInterval->totalSeconds->toBe(RetryOptions::DEFAULT_INITIAL_INTERVAL)
            ->retryOptions->backoffCoefficient->toBe(RetryOptions::DEFAULT_BACKOFF_COEFFICIENT)
            ->retryOptions->maximumInterval->totalSeconds->toBe(RetryOptions::DEFAULT_MAXIMUM_INTERVAL)
            ->retryOptions->maximumAttempts->toBe(RetryOptions::DEFAULT_MAXIMUM_ATTEMPTS);

        return true;
    });

    Temporal::partialMock()
        ->shouldReceive('getTemporalContext')
        ->andReturn($contextMock);

    $builder = Temporal::newChildWorkflow();

    expect($builder)
        ->namespace->toBe(ClientOptions::DEFAULT_NAMESPACE)
        ->taskQueue->toBe(WorkerFactory::DEFAULT_TASK_QUEUE)
        ->retryOptions->not->toBeNull()
        ->retryOptions->initialInterval->totalSeconds->toBe(RetryOptions::DEFAULT_INITIAL_INTERVAL)
        ->retryOptions->backoffCoefficient->toBe(RetryOptions::DEFAULT_BACKOFF_COEFFICIENT)
        ->retryOptions->maximumInterval->totalSeconds->toBe(RetryOptions::DEFAULT_MAXIMUM_INTERVAL)
        ->retryOptions->maximumAttempts->toBe(RetryOptions::DEFAULT_MAXIMUM_ATTEMPTS);

    if ($typed) {
        $builder->build(DemoWorkflowInterface::class);
    } else {
        $builder->buildUntyped('demo.greet');
    }
})->with([
    'typed' => true,
    'untyped' => false,
]);

it('can build child workflow with custom config options', function (bool $typed) {
    config()->set('temporal.namespace', 'test-namespace');
    config()->set('temporal.queue', 'test-queue');
    config()->set('temporal.retry.workflow', [
        'initial_interval' => 5,
        'backoff_coefficient' => 6.0,
        'maximum_interval' => 500,
        'maximum_attempts' => 10,
    ]);

    $contextMock = mockTemporalContext(validateWorkflowOptions: function (ChildWorkflowOptions $workflowOptions): bool {
        expect($workflowOptions)
            ->namespace->toBe('test-namespace')
            ->taskQueue->toBe('test-queue')
            ->retryOptions->not->toBeNull()
            ->retryOptions->initialInterval->totalSeconds->toBe(5)
            ->retryOptions->backoffCoefficient->toBe(6.0)
            ->retryOptions->maximumInterval->totalSeconds->toBe(500)
            ->retryOptions->maximumAttempts->toBe(10);

        return true;
    });

    Temporal::partialMock()
        ->shouldReceive('getTemporalContext')
        ->andReturn($contextMock);

    $builder = Temporal::newChildWorkflow();

    expect($builder)
        ->namespace->toBe('test-namespace')
        ->taskQueue->toBe('test-queue')
        ->retryOptions->not->toBeNull()
        ->retryOptions->initialInterval->totalSeconds->toBe(5)
        ->retryOptions->backoffCoefficient->toBe(6.0)
        ->retryOptions->maximumInterval->totalSeconds->toBe(500)
        ->retryOptions->maximumAttempts->toBe(10);

    if ($typed) {
        $builder->build(DemoWorkflowInterface::class);
    } else {
        $builder->buildUntyped('demo.greet');
    }
})->with([
    'typed' => true,
    'untyped' => false,
]);

it('can build child workflow with custom options', function (bool $typed) {
    $contextMock = mockTemporalContext(validateWorkflowOptions: function (ChildWorkflowOptions $workflowOptions): bool {
        expect($workflowOptions)
            ->namespace->toBe('custom-namespace')
            ->taskQueue->toBe('custom-queue')
            ->workflowExecutionTimeout->totalSeconds->toBe(10)
            ->retryOptions->not->toBeNull()
            ->retryOptions->maximumAttempts->toBe(5);

        return true;
    });

    Temporal::partialMock()
        ->shouldReceive('getTemporalContext')
        ->andReturn($contextMock);

    $builder = Temporal::newChildWorkflow()
        ->withWorkflowExecutionTimeout(CarbonInterval::seconds(10))
        ->withRetryOptions(RetryOptions::new()->withMaximumAttempts(5))
        ->withNamespace('custom-namespace')
        ->withTaskQueue('custom-queue');

    expect($builder)
        ->namespace->toBe('custom-namespace')
        ->taskQueue->toBe('custom-queue')
        ->workflowExecutionTimeout->totalSeconds->toBe(10)
        ->retryOptions->not->toBeNull()
        ->retryOptions->maximumAttempts->toBe(5);

    if ($typed) {
        $builder->build(DemoWorkflowInterface::class);
    } else {
        $builder->buildUntyped('demo.greet');
    }
})->with([
    'typed' => true,
    'untyped' => false,
]);
