<?php

namespace Keepsuit\LaravelTemporal\Testing;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use PharData;
use RuntimeException;
use Spiral\RoadRunner\Console\Environment\Architecture;
use Spiral\RoadRunner\Console\Environment\OperatingSystem;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class LocalTemporalServer implements TemporalServer
{
    protected const TEMPORAL_CLI_RELEASES = 'https://api.github.com/repos/temporalio/cli/releases/latest';

    protected ?string $binaryPath = null;

    protected ?Process $temporalServerProcess = null;

    final public function __construct(
        protected Output $output,
        protected bool $debug = false
    ) {
    }

    public static function create(): static
    {
        return new static(
            new ConsoleOutput()
        );
    }

    public function setDebugOutput(bool $debug): static
    {
        $this->debug = $debug;

        return $this;
    }

    public function start(?int $port = null): void
    {
        $binaryPath = $this->ensureTemporalCliIsInstalled();

        $this->startServer($binaryPath, $port);
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

    protected function ensureTemporalCliIsInstalled(): string
    {
        if ($this->binaryPath !== null) {
            return $this->binaryPath;
        }

        if (file_exists(base_path('temporal'))) {
            return $this->binaryPath = base_path('temporal');
        }

        $binaryPath = (new ExecutableFinder())->find('temporal', null, [base_path()]);

        if ($binaryPath) {
            return $this->binaryPath = $binaryPath;
        }

        return $this->binaryPath = $this->downloadTemporalCli();
    }

    protected function downloadTemporalCli(): string
    {
        $os = OperatingSystem::createFromGlobals();
        $arch = Architecture::createFromGlobals();

        $client = HttpClient::create();
        $response = $client->request('GET', self::TEMPORAL_CLI_RELEASES);
        $content = $response->toArray();

        $asset = Arr::first($content['assets'], fn (array $asset) => rescue(
            fn () => \Safe\preg_match(sprintf('#temporal_cli_.+_%s_%s\..+#', $os, $arch), $asset['name']) === 1,
            false
        ));

        if ($asset === null) {
            throw new RuntimeException(sprintf('Could not find a Temporal CLI binary for %s %s', $os, $arch));
        }

        $response = $client->request('GET', $asset['browser_download_url']);

        $assetPath = base_path($asset['name']);
        \Safe\file_put_contents($assetPath, $response->getContent());

        $filename = match ($os) {
            OperatingSystem::OS_WINDOWS => 'temporal.exe',
            default => 'temporal',
        };

        $phar = new PharData($assetPath);
        $phar->extractTo(base_path(), [$filename], true);
        \Safe\unlink($phar->getPath());

        return base_path($filename);
    }

    protected function startServer(string $binaryPath, ?int $port): void
    {
        $this->debugOutput('Starting Temporal server... ', newLine: false);

        $temporalAddress = config('temporal.address', '127.0.0.1:7233');
        $temporalPort = $port ?? (int) \Safe\parse_url((string) $temporalAddress, PHP_URL_PORT);

        $this->temporalServerProcess = new Process(
            command: [
                $binaryPath,
                'server',
                'start-dev',
                '-p',
                (string) $temporalPort,
                '--log-level',
                'error',
            ],
            timeout: 10
        );

        $this->temporalServerProcess->start();

        try {
            $serverStarted = $this->temporalServerProcess->waitUntil(
                fn ($type, $output) => Str::contains((string) $output, [
                    'http server started',
                    'Temporal server is running',
                    'Temporal server:',
                    'Server:',
                ])
            );
        } catch (\Throwable) {
            $serverStarted = false;
        }

        if (! $serverStarted) {
            $this->debugOutput('<error>error</error>');
            $this->debugOutput($this->temporalServerProcess->getErrorOutput());

            throw new RuntimeException(sprintf('Failed to start Temporal test server: %s', $this->temporalServerProcess->getErrorOutput()));
        }

        $this->debugOutput('<info>done.</info>');
        $this->debugOutput($this->temporalServerProcess->getOutput());
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
