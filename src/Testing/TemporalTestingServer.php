<?php

namespace Keepsuit\LaravelTemporal\Testing;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Process\Process;
use Temporal\Testing\Downloader;
use Temporal\Testing\SystemInfo;

class TemporalTestingServer
{
    protected ?Process $temporalServerProcess = null;

    public function __construct(
        protected Output $output,
        protected Downloader $downloader,
        protected SystemInfo $systemInfo,
        protected bool $debug = false
    ) {
    }

    public static function create(bool $debug = false): self
    {
        return new self(
            new ConsoleOutput(),
            new Downloader(new Filesystem(), HttpClient::create()),
            SystemInfo::detect(),
            $debug
        );
    }

    public function setDebugOutput(bool $debug): self
    {
        $this->debug = $debug;

        return $this;
    }

    public function start(int $port = 7233): void
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
        $this->temporalServerProcess?->stop();
    }

    protected function startTemporalServer(int $port = 7233): void
    {
        $this->debugOutput('Starting Temporal test server... ', newLine: false);

        $this->temporalServerProcess = new Process(
            command: [$this->systemInfo->temporalServerExecutable, (string) $port, '--enable-time-skipping'],
            timeout: 10
        );

        $this->temporalServerProcess->start();

        sleep(1);

        if (! $this->temporalServerProcess->isRunning()) {
            $this->output->writeln('<error>error</error>');
            $this->output->writeln('Error starting Temporal server: '.$this->temporalServerProcess->getErrorOutput());
            exit(1);
        }

        $this->debugOutput('<info>done.</info>');
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
