<?php

namespace Keepsuit\LaravelTemporal\Testing;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Keepsuit\LaravelTemporal\Support\RoadRunnerBinaryHelper;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Temporal\Testing\Downloader;
use Temporal\Testing\SystemInfo;

class TemporalTestingEnvironment
{
    protected TemporalTestingServer $temporalServer;

    protected ?Process $roadRunnerProcess = null;

    public function __construct(
        protected Output $output,
        protected Downloader $downloader,
        protected SystemInfo $systemInfo,
        protected RoadRunnerBinaryHelper $roadRunnerBinary,
        protected bool $debug = false
    ) {
        $this->temporalServer = TemporalTestingServer::create();
    }

    public static function create(bool $debug = false): self
    {
        return new self(
            new ConsoleOutput(),
            new Downloader(new Filesystem(), HttpClient::create()),
            SystemInfo::detect(),
            new RoadRunnerBinaryHelper(),
            $debug
        );
    }

    public static function bootstrap(Application $app, bool $onlyWorker = false): void
    {
        $app->make(Kernel::class)->bootstrap();

        $env = static::create();

        $env->start($onlyWorker);

        register_shutdown_function(fn () => $env->stop());
    }

    public function setDebugOutput(bool $debug): self
    {
        $this->debug = $debug;

        return $this;
    }

    public function start(bool $onlyWorker = false): void
    {
        if (! $onlyWorker) {
            $temporalAddress = config('temporal.address', '127.0.0.1:7233');
            $temporalPort = parse_url($temporalAddress, PHP_URL_PORT);

            $this->temporalServer->setDebugOutput($this->debug)->start($temporalPort);
        }

        $this->downloadRoadRunnerBinary();

        $this->startTemporalWorker();
    }

    public function stop(): void
    {
        $this->temporalServer->stop();
        $this->roadRunnerProcess?->stop();
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
                'TEMPORAL_TESTING_ENV' => 1
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
            fn ($type, $output) => str_contains($output, 'RoadRunner server started')
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
