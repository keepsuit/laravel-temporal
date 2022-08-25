<?php

namespace Keepsuit\LaravelTemporal\Support;

use Symfony\Component\Process\Process;

class SymfonyProcessFactory
{
    /**
     * Create a new Symfony process instance.
     *
     * @param  string[]  $command
     * @param  array<string,string|int|bool>|null  $env
     */
    public function createProcess(array $command, string $cwd = null, array $env = null, mixed $input = null, ?float $timeout = 60): Process
    {
        return new Process($command, $cwd, $env, $input, $timeout);
    }
}
