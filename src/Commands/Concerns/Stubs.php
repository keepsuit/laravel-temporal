<?php

namespace Keepsuit\LaravelTemporal\Commands\Concerns;

use Illuminate\Console\Command;

/**
 * @mixin Command
 */
trait Stubs
{
    /**
     * Resolve the fully-qualified path to the stub.
     */
    protected function resolveStubPath(string $stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.'/../stubs/'.$stub;
    }
}
