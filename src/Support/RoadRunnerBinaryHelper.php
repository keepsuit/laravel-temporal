<?php

namespace Keepsuit\LaravelTemporal\Support;

use Illuminate\Support\Str;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class RoadRunnerBinaryHelper
{
    protected ?string $binaryPath = null;

    /**
     * Find the RoadRunner binary used by the application.
     */
    public function binaryPath(): ?string
    {
        if ($this->binaryPath !== null) {
            return $this->binaryPath;
        }

        if (file_exists(base_path('rr'))) {
            return $this->binaryPath = base_path('rr');
        }

        $roadRunnerBinary = (new ExecutableFinder())->find('rr', null, [base_path()]);

        if ($roadRunnerBinary === null) {
            return null;
        }

        if (Str::contains($roadRunnerBinary, 'vendor/bin/rr')) {
            return null;
        }

        return $this->binaryPath = $roadRunnerBinary;
    }

    public function download(bool $force = false): void
    {
        if (! $force && $this->binaryPath() !== null) {
            return;
        }

        $process = new Process(array_filter([
            (new PhpExecutableFinder())->find(),
            './vendor/bin/rr',
            'get-binary',
            '-n',
            '--ansi',
        ]), base_path(), null, null, null);

        $process->mustRun();

        chmod(base_path('rr'), 0755);
    }

    public function ensureConfigFileExists(): void
    {
        if (! file_exists(base_path('.rr.yaml'))) {
            touch(base_path('.rr.yaml'));
        }
    }

    public function configVersion(): string
    {
        $version = tap(new Process([$this->binaryPath(), '--version'], base_path()))
            ->run()
            ->getOutput();

        $version = explode(' ', (string) $version)[2];

        if (version_compare($version, '2023.1', '>')) {
            return '3';
        }

        if (version_compare($version, '2.0', '>')) {
            return '2.7';
        }

        throw new \RuntimeException(sprintf('Your RoadRunner binary version (%s) is not compatible with laravel temporal.', $version));
    }
}
