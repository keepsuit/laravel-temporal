<?php

use Carbon\CarbonInterval;
use Keepsuit\LaravelTemporal\Facade\Temporal;
use Keepsuit\LaravelTemporal\Tests\Fixtures\WorkflowDiscovery\Activities\DemoActivityInterface;
use Temporal\Activity\LocalActivityOptions;
use Temporal\Common\RetryOptions;

beforeEach(function () {
    Temporal::fake();
});

it('can build local activity with default config options', function (bool $typed) {
    $contextMock = mockTemporalContext(validateActivityOptions: function (LocalActivityOptions $activityOptions): bool {
        expect($activityOptions)
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

    $builder = Temporal::newLocalActivity();

    if ($typed) {
        $builder->build(DemoActivityInterface::class);
    } else {
        $builder->buildUntyped();
    }
})->with([
    'typed' => true,
    'untyped' => false,
]);

it('can build local activity with custom config options', function (bool $typed) {
    config()->set('temporal.retry.activity', [
        'initial_interval' => 5,
        'backoff_coefficient' => 6.0,
        'maximum_interval' => 500,
        'maximum_attempts' => 10,
    ]);

    $contextMock = mockTemporalContext(validateActivityOptions: function (LocalActivityOptions $activityOptions): bool {
        expect($activityOptions)
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

    $builder = Temporal::newLocalActivity();

    if ($typed) {
        $builder->build(DemoActivityInterface::class);
    } else {
        $builder->buildUntyped();
    }
})->with([
    'typed' => true,
    'untyped' => false,
]);

it('can build local activity with custom options', function (bool $typed) {
    $contextMock = mockTemporalContext(validateActivityOptions: function (LocalActivityOptions $activityOptions): bool {
        expect($activityOptions)
            ->startToCloseTimeout->totalSeconds->toBe(10)
            ->retryOptions->not->toBeNull()
            ->retryOptions->maximumAttempts->toBe(5);

        return true;
    });

    Temporal::partialMock()
        ->shouldReceive('getTemporalContext')
        ->andReturn($contextMock);

    $builder = Temporal::newLocalActivity()
        ->withStartToCloseTimeout(CarbonInterval::seconds(10))
        ->withRetryOptions(RetryOptions::new()->withMaximumAttempts(5));

    if ($typed) {
        $builder->build(DemoActivityInterface::class);
    } else {
        $builder->buildUntyped();
    }
})->with([
    'typed' => true,
    'untyped' => false,
]);
