<?php

namespace Keepsuit\LaravelTemporal\Support;

use Illuminate\Support\Str;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class RoadRunnerBinaryHelper
{
    /**
     * Find the RoadRunner binary used by the application.
     */
    public function binaryPath(): ?string
    {
        if (file_exists(base_path('rr'))) {
            return base_path('rr');
        }

        $roadRunnerBinary = (new ExecutableFinder())->find('rr', null, [base_path()]);

        if ($roadRunnerBinary === null) {
            return null;
        }

        if (Str::contains($roadRunnerBinary, 'vendor/bin/rr')) {
            return null;
        }

        return $roadRunnerBinary;
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
}
