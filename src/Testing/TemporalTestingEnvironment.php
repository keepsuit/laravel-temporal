<?php

namespace Keepsuit\LaravelTemporal\Testing;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Keepsuit\LaravelTemporal\Support\SymfonyProcessFactory;
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
    protected ?Process $temporalServerProcess = null;

    protected ?Process $roadRunnerProcess = null;

    public function __construct(
        protected Output $output,
        protected Downloader $downloader,
        protected SystemInfo $systemInfo
    ) {
    }

    public static function create(): self
    {
        return new self(
            new ConsoleOutput(),
            new Downloader(new Filesystem(), HttpClient::create()),
            SystemInfo::detect(),
        );
    }

    public static function bootstrap(Application $app): void
    {
        $app->make(Kernel::class)->bootstrap();

        $env = static::create();

        $env->start();

        register_shutdown_function(fn () => $env->stop());
    }

    public function start(): void
    {
        $this->downloadTemporalServerExecutable();

        $this->startTemporalServer();

        $this->startTemporalWorker();
    }

    protected function downloadTemporalServerExecutable(): void
    {
        if ($this->downloader->check($this->systemInfo->temporalServerExecutable)) {
            return;
        }

        $this->output->write('Download temporal test server... ');

        $this->downloader->download($this->systemInfo);

        $this->output->writeln('<info>done.</info>');
    }

    public function stop(): void
    {
        $this->temporalServerProcess?->stop();
        $this->roadRunnerProcess?->stop();
    }

    protected function startTemporalServer(): void
    {
        $temporalAddress = config('temporal.address', '127.0.0.1:7233');
        $temporalPort = parse_url($temporalAddress, PHP_URL_PORT);

        $this->output->write('Starting Temporal test server... ');

        $this->temporalServerProcess = new Process(
            [$this->systemInfo->temporalServerExecutable, $temporalPort, '--enable-time-skipping']
        );

        $this->temporalServerProcess = (new SymfonyProcessFactory())->createProcess(
            command: [$this->systemInfo->temporalServerExecutable, (string) $temporalPort, '--enable-time-skipping'],
            timeout: 10
        );
        $this->temporalServerProcess->start();

        $this->output->writeln('<info>done.</info>');

        sleep(1);

        if (! $this->temporalServerProcess->isRunning()) {
            $this->output->writeln('<error>error</error>');
            $this->output->writeln('Error starting Temporal server: '.$this->temporalServerProcess->getErrorOutput());
            exit(1);
        }
    }

    protected function startTemporalWorker(): void
    {
        $this->roadRunnerProcess = (new SymfonyProcessFactory())->createProcess(
            command: [
                $this->systemInfo->rrExecutable,
                ...['-o', 'version=2.7'],
                ...['-o', sprintf('server.command=%s ./vendor/bin/roadrunner-temporal-test-worker', (new PhpExecutableFinder())->find())],
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
            ])
        );

        $this->output->write('Starting RoadRunner... ');

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

        $this->output->writeln('<info>done.</info>');
    }
}
