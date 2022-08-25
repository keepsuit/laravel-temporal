<?php

namespace Keepsuit\LaravelTemporal\Support;

use Illuminate\Support\Str;
use Symfony\Component\Process\ExecutableFinder;

class RoadRunnerBinaryFinder
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
}
