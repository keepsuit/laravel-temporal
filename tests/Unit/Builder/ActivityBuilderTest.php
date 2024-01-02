<?php

use Carbon\CarbonInterval;
use Keepsuit\LaravelTemporal\Facade\Temporal;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivityInterface;
use Temporal\Activity\ActivityOptions;
use Temporal\Common\RetryOptions;
use Temporal\WorkerFactory;

beforeEach(function () {
    Temporal::fake();
});

it('can build activity with default config options', function (bool $typed) {
    $contextMock = mockTemporalContext(validateActivityOptions: function (ActivityOptions $activityOptions): bool {
        expect($activityOptions)
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

    $builder = Temporal::newActivity();

    expect($builder)
        ->taskQueue->toBe(WorkerFactory::DEFAULT_TASK_QUEUE)
        ->retryOptions->not->toBeNull()
        ->retryOptions->initialInterval->totalSeconds->toBe(RetryOptions::DEFAULT_INITIAL_INTERVAL)
        ->retryOptions->backoffCoefficient->toBe(RetryOptions::DEFAULT_BACKOFF_COEFFICIENT)
        ->retryOptions->maximumInterval->totalSeconds->toBe(RetryOptions::DEFAULT_MAXIMUM_INTERVAL)
        ->retryOptions->maximumAttempts->toBe(RetryOptions::DEFAULT_MAXIMUM_ATTEMPTS);

    if ($typed) {
        $builder->build(DemoActivityInterface::class);
    } else {
        $builder->buildUntyped();
    }
})->with([
    'typed' => true,
    'untyped' => false,
]);

it('can build activity with custom config options', function (bool $typed) {
    config()->set('temporal.queue', 'test-queue');
    config()->set('temporal.retry.activity', [
        'initial_interval' => 5,
        'backoff_coefficient' => 6.0,
        'maximum_interval' => 500,
        'maximum_attempts' => 10,
    ]);

    $contextMock = mockTemporalContext(validateActivityOptions: function (ActivityOptions $activityOptions): bool {
        expect($activityOptions)
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

    $builder = Temporal::newActivity();

    expect($builder)
        ->taskQueue->toBe('test-queue')
        ->retryOptions->not->toBeNull()
        ->retryOptions->initialInterval->totalSeconds->toBe(5)
        ->retryOptions->backoffCoefficient->toBe(6.0)
        ->retryOptions->maximumInterval->totalSeconds->toBe(500)
        ->retryOptions->maximumAttempts->toBe(10);

    if ($typed) {
        $builder->build(DemoActivityInterface::class);
    } else {
        $builder->buildUntyped();
    }
})->with([
    'typed' => true,
    'untyped' => false,
]);

it('can build activity with custom options', function (bool $typed) {
    $contextMock = mockTemporalContext(validateActivityOptions: function (ActivityOptions $activityOptions): bool {
        expect($activityOptions)
            ->taskQueue->toBe('custom-queue')
            ->startToCloseTimeout->totalSeconds->toBe(10)
            ->retryOptions->not->toBeNull()
            ->retryOptions->maximumAttempts->toBe(5);

        return true;
    });

    Temporal::partialMock()
        ->shouldReceive('getTemporalContext')
        ->andReturn($contextMock);

    $builder = Temporal::newActivity()
        ->withStartToCloseTimeout(CarbonInterval::seconds(10))
        ->withRetryOptions(RetryOptions::new()->withMaximumAttempts(5))
        ->withTaskQueue('custom-queue');

    expect($builder)
        ->taskQueue->toBe('custom-queue')
        ->startToCloseTimeout->totalSeconds->toBe(10)
        ->retryOptions->not->toBeNull()
        ->retryOptions->maximumAttempts->toBe(5);

    if ($typed) {
        $builder->build(DemoActivityInterface::class);
    } else {
        $builder->buildUntyped();
    }
})->with([
    'typed' => true,
    'untyped' => false,
]);
