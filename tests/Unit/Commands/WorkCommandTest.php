<?php

use Illuminate\Console\OutputStyle;
use Keepsuit\LaravelTemporal\Commands\WorkCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;

it('prints worker output logged with info level by any RoadRunner version', function (string $level) {
    $buffer = new BufferedOutput;

    $command = new WorkCommand;
    $command->setOutput(new OutputStyle(new ArrayInput([]), $buffer));

    (fn () => $this->handleServerLog(['level' => $level, 'msg' => 'worker output', 'logger' => 'server']))->call($command);

    expect($buffer->fetch())->toContain('worker output');
})->with([
    'RoadRunner 2025.x' => ['info'],
    'RoadRunner 3.x' => ['INFO'],
]);

it('skips worker output logged with debug level', function () {
    $buffer = new BufferedOutput;

    $command = new WorkCommand;
    $command->setOutput(new OutputStyle(new ArrayInput([]), $buffer));

    (fn () => $this->handleServerLog(['level' => 'DEBUG', 'msg' => 'worker output', 'logger' => 'server']))->call($command);

    expect($buffer->fetch())->toBe('');
});

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
