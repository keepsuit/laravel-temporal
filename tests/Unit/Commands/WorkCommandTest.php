<?php

use Keepsuit\LaravelTemporal\Commands\WorkCommand;
use Symfony\Component\Process\Process;

it('subscribes to SIGINT and SIGTERM', function () {
    expect((new WorkCommand)->getSubscribedSignals())
        ->toBe([SIGINT, SIGTERM]);
});

it('gracefully stops the server on SIGTERM using the configured grace period', function () {
    config()->set('temporal.shutdown_grace_period', 42);

    $server = mock(Process::class);
    $server->shouldReceive('stop')->once()->with(42);

    $command = new WorkCommand;
    (fn () => $this->server = $server)->call($command);

    expect($command->handleSignal(SIGTERM))->toBe(0);
});

it('gracefully stops the server on SIGINT', function () {
    config()->set('temporal.shutdown_grace_period', 10);

    $server = mock(Process::class);
    $server->shouldReceive('stop')->once()->with(10);

    $command = new WorkCommand;
    (fn () => $this->server = $server)->call($command);

    expect($command->handleSignal(SIGINT))->toBe(0);
});

it('returns the previous exit code when handling a signal', function () {
    $server = mock(Process::class);
    $server->shouldReceive('stop')->once();

    $command = new WorkCommand;
    (fn () => $this->server = $server)->call($command);

    expect($command->handleSignal(SIGTERM, 3))->toBe(3);
});

it('defers unsubscribed signals to the parent handler', function () {
    $server = mock(Process::class);
    $server->shouldNotReceive('stop');

    $command = new WorkCommand;
    (fn () => $this->server = $server)->call($command);

    expect($command->handleSignal(SIGHUP))->toBeFalse();
});
