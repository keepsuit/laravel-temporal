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

    public function configVersion(): string
    {
        $binaryPath = $this->binaryPath();

        if ($binaryPath === null) {
            throw new \RuntimeException('RoadRunner binary not found.');
        }

        $version = tap(new Process([$binaryPath, '--version'], base_path()))
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

    public function download(bool $force = false): string
    {
        if (! $force && $this->binaryPath() !== null) {
            return $this->binaryPath();
        }

        $process = new Process(array_filter([
            (new PhpExecutableFinder())->find(),
            './vendor/bin/rr',
            'get-binary',
            '-n',
            '--ansi',
        ]), base_path(), null, null, null);

        $process->mustRun();

        $this->binaryPath = base_path('rr');

        \Safe\chmod($this->binaryPath, 0755);
        \Safe\touch(base_path('.rr.yaml'));

        return $this->binaryPath;
    }

    public function ensureConfigFileExists(): void
    {
        if (! file_exists(base_path('.rr.yaml'))) {
            \Safe\touch(base_path('.rr.yaml'));
        }
    }
}
