<?php

namespace Keepsuit\LaravelTemporal\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ActivityMakeCommand extends GeneratorCommand
{
    protected $signature = 'make:activity {name}
                            {--interface : Create an interface for the activity instead of a class}
                            {--local : Create a local activity}
                            {--scoped : Create the activity inside a scoped directory}
                            {--for-workflow= : Create the activity in the provided workflow namespace}';

    protected $description = 'Create a temporal activity';

    protected $type = 'Activity';

    protected function getStub(): string
    {
        return match (true) {
            $this->option('interface') && $this->option('local') => $this->resolveStubPath('/stubs/local_activity_interface.stub'),
            $this->option('interface') => $this->resolveStubPath('/stubs/activity_interface.stub'),
            $this->option('local') => $this->resolveStubPath('/stubs/local_activity.stub'),
            default => $this->resolveStubPath('/stubs/activity.stub'),
        };
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        $rootNamespace = match (true) {
            is_dir($this->laravel->path('Temporal/Activities')) => $rootNamespace.'\\Temporal',
            ! is_dir($this->laravel->path('Activities')) => $rootNamespace.'\\Temporal',
            default => $rootNamespace
        };

        if ($this->option('for-workflow') !== null) {
            $namespace = Str::of($this->option('for-workflow'))
                ->whenEndsWith('Workflow', fn ($name) => $name->replaceLast('Workflow', ''));

            return sprintf('%s\\Workflows\\%s', $rootNamespace, $namespace);
        }

        if ($this->option('scoped')) {
            $namespace = Str::of($this->getNameInput())
                ->whenEndsWith('Activity', fn ($name) => $name->replaceLast('Activity', ''));

            return sprintf('%s\\Activities\\%s', $rootNamespace, $namespace);
        }

        return sprintf('%s\\Activities', $rootNamespace);
    }

    /**
     * Resolve the fully-qualified path to the stub.
     */
    protected function resolveStubPath(string $stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.$stub;
    }

    protected function getNameInput(): string
    {
        return Str::of(parent::getNameInput())
            ->whenEndsWith('Interface', fn ($name) => $name->replaceLast('Interface', ''))
            ->when($this->option('interface'), fn ($name) => $name->append('Interface'));
    }
}
