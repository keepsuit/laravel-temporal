<?php

namespace Keepsuit\LaravelTemporal\Testing;

use Illuminate\Support\Str;
use Keepsuit\LaravelTemporal\Support\RoadRunnerBinaryHelper;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class TemporalTestingWorker
{
    protected ?Process $roadRunnerProcess = null;

    public function __construct(
        protected Output $output,
        protected RoadRunnerBinaryHelper $roadRunnerBinary,
        protected bool $debug = false
    ) {
    }

    public static function create(bool $debug = false): self
    {
        return new self(
            new ConsoleOutput(),
            new RoadRunnerBinaryHelper(),
            $debug
        );
    }

    public function setDebugOutput(bool $debug): self
    {
        $this->debug = $debug;

        return $this;
    }

    public function start(): void
    {
        $this->downloadRoadRunnerBinary();

        $this->startTemporalWorker();
    }

    public function stop(): void
    {
        if ($this->roadRunnerProcess != null) {
            $this->roadRunnerProcess->signal(SIGTERM);
            usleep(100_000);
        }
    }

    public function isRunning(): bool
    {
        return $this->roadRunnerProcess?->isRunning() ?? false;
    }

    protected function startTemporalWorker(): void
    {
        $this->roadRunnerProcess = new Process(
            command: [
                $this->roadRunnerBinary->binaryPath(),
                ...['-o', 'version=2.7'],
                ...['-o', sprintf('server.command=%s ./vendor/bin/roadrunner-temporal-worker', (new PhpExecutableFinder())->find())],
                ...['-o', sprintf('temporal.address=%s', config('temporal.address'))],
                ...['-o', sprintf('temporal.activities.num_workers=%s', 1)],
                ...['-o', sprintf('rpc.listen=tcp://127.0.0.1:%d', 6001)],
                ...['-o', 'logs.mode=none'],
                ...['-o', 'kv.test.driver=memory'],
                ...['-o', 'kv.test.config.interval=10'],
                'serve',
            ],
            timeout: 10,
            cwd: base_path(),
            env: array_merge($_SERVER, $_ENV, [
                'APP_ENV' => app()->environment(),
                'APP_BASE_PATH' => base_path(),
                'LARAVEL_TEMPORAL' => 1,
                'TEMPORAL_QUEUE' => config('temporal.queue'),
                'TEMPORAL_TESTING_ENV' => 1,
            ])
        );

        $this->debugOutput('Starting RoadRunner... ', newLine: false);

        $this->roadRunnerProcess->start();

        if (! $this->roadRunnerProcess->isRunning()) {
            $this->output->writeln('<error>error</error>');
            $this->output->writeln('Error starting RoadRunner: '.$this->roadRunnerProcess->getErrorOutput());
            exit(1);
        }

        $roadRunnerStarted = $this->roadRunnerProcess->waitUntil(
            fn ($type, $output) => Str::contains((string) $output, 'RoadRunner server started')
        );

        if (! $roadRunnerStarted) {
            $this->output->writeln('<error>error</error>');
            $this->output->writeln('Error starting RoadRunner: '.$this->roadRunnerProcess->getErrorOutput());
            exit(1);
        }

        $this->debugOutput('<info>done.</info>');
    }

    protected function downloadRoadRunnerBinary(): void
    {
        $this->roadRunnerBinary->ensureConfigFileExists();

        if ($this->roadRunnerBinary->binaryPath() !== null) {
            return;
        }

        $this->debugOutput('Download roadrunner binary... ', newLine: false);

        $this->roadRunnerBinary->download();

        $this->debugOutput('<info>done.</info>');
    }

    protected function debugOutput(string $message, bool $newLine = true): void
    {
        if (! $this->debug) {
            return;
        }

        if ($newLine) {
            $this->output->writeln($message);
        } else {
            $this->output->write($message);
        }
    }
}
