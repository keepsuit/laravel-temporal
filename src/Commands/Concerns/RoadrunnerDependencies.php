<?php

namespace Keepsuit\LaravelTemporal\Commands\Concerns;

use Illuminate\Console\Command;
use Keepsuit\LaravelTemporal\Support\RoadRunnerBinaryHelper;
use RuntimeException;

/**
 * @mixin Command
 */
trait RoadrunnerDependencies
{
    protected function ensureRoadRunnerBinaryIsInstalled(RoadRunnerBinaryHelper $roadRunnerBinaryHelper): string
    {
        $binaryPath = $roadRunnerBinaryHelper->binaryPath();

        if ($binaryPath !== null) {
            return $binaryPath;
        }

        if ($this->confirm('Unable to locate RoadRunner binary. Should we download the binary for your operating system?', true)) {
            return $roadRunnerBinaryHelper->download(true);
        }

        throw new RuntimeException('Unable to locate RoadRunner binary.');
    }
}
