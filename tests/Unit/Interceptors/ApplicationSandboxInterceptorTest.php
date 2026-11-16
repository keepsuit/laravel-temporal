<?php

use Illuminate\Contracts\Debug\ExceptionHandler;
use Keepsuit\LaravelTemporal\Interceptors\ApplicationSandboxInterceptor;
use Keepsuit\LaravelTemporal\Support\CurrentApplication;
use Temporal\DataConverter\EncodedValues;
use Temporal\Exception\Client\ActivityCanceledException;
use Temporal\Exception\Client\ActivityPausedException;
use Temporal\Exception\Client\ActivityResetException;
use Temporal\Interceptor\ActivityInbound\ActivityInput;
use Temporal\Interceptor\Header;

beforeEach(fn () => CurrentApplication::setRootApp($this->app));

afterEach(fn () => CurrentApplication::setRootApp(null));

it('reports activity exceptions and rethrows them', function (Throwable $exception, bool $reported) {
    $handler = $this->mock(ExceptionHandler::class);
    $handler->shouldReceive('report')->times($reported ? 1 : 0)->with($exception);

    expect(fn () => (new ApplicationSandboxInterceptor)->handleActivityInbound(
        new ActivityInput(EncodedValues::empty(), Header::empty()),
        fn () => throw $exception,
    ))->toThrow($exception::class);
})->with([
    'failure' => [new RuntimeException('failed'), true],
    'canceled' => [new ActivityCanceledException, false],
    'paused' => [new ActivityPausedException, false],
    'reset' => [new ActivityResetException, false],
]);
