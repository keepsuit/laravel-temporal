<?php

namespace Keepsuit\LaravelTemporal\Testing;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Process\Process;
use Temporal\Testing\Downloader;
use Temporal\Testing\SystemInfo;

class TimeSkippingTemporalServer implements TemporalServer
{
    protected ?Process $temporalServerProcess = null;

    final public function __construct(
        protected Output $output,
        protected Downloader $downloader,
        protected SystemInfo $systemInfo,
        protected bool $debug = false
    ) {}

    public static function create(): static
    {
        return new static(
            new ConsoleOutput,
            new Downloader(new Filesystem, HttpClient::create()),
            SystemInfo::detect(),
        );
    }

    public function setDebugOutput(bool $debug): static
    {
        $this->debug = $debug;

        return $this;
    }

    public function start(?int $port = null): void
    {
        $this->downloadTemporalServerExecutable();

        $this->startTemporalServer($port);
    }

    public function isRunning(): bool
    {
        return $this->temporalServerProcess?->isRunning() ?? false;
    }

    public function stop(): void
    {
        if ($this->temporalServerProcess === null) {
            return;
        }

        $this->temporalServerProcess->signal(SIGTERM);

        do {
            usleep(1_000);
        } while ($this->temporalServerProcess->isRunning());
    }

    protected function startTemporalServer(?int $port = null): void
    {
        $this->debugOutput('Starting Temporal test server... ', newLine: false);

        $temporalAddress = config('temporal.address', '127.0.0.1:7233');
        $temporalPort = $port ?? (int) \Safe\parse_url((string) $temporalAddress, PHP_URL_PORT);

        $this->temporalServerProcess = new Process(
            command: [$this->systemInfo->temporalServerExecutable, (string) $temporalPort, '--enable-time-skipping'],
            timeout: 10
        );

        $this->temporalServerProcess->start();

        usleep(10_000);

        if (! $this->temporalServerProcess->isRunning()) {
            $this->debugOutput('<error>error</error>');
            $this->debugOutput($this->temporalServerProcess->getErrorOutput());

            throw new \RuntimeException(sprintf('Failed to start Temporal test server: %s', $this->temporalServerProcess->getErrorOutput()));
        }

        $this->debugOutput('<info>done.</info>');
        $this->debugOutput($this->temporalServerProcess->getOutput());
    }

    protected function downloadTemporalServerExecutable(): void
    {
        if ($this->downloader->check($this->systemInfo->temporalServerExecutable)) {
            return;
        }

        $this->debugOutput('Download temporal test server... ', newLine: false);

        $this->downloader->download($this->systemInfo);

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
