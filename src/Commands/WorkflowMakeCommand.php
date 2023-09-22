<?php

namespace Keepsuit\LaravelTemporal\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class WorkflowMakeCommand extends GeneratorCommand
{
    protected $signature = 'make:workflow {name}
                            {--interface : Create an interface for the workflow instead of a class}
                            {--scoped : Create the workflow inside a scoped directory}';

    protected $description = 'Create a temporal workflow class';

    protected $type = 'Workflow';

    protected function getStub(): string
    {
        return match (true) {
            $this->option('interface') => $this->resolveStubPath('/stubs/workflow_interface.stub'),
            default => $this->resolveStubPath('/stubs/workflow.stub'),
        };
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        $rootNamespace = match (true) {
            is_dir($this->laravel->path('Temporal/Workflows')) => $rootNamespace.'\\Temporal',
            ! is_dir($this->laravel->path('Workflows')) => $rootNamespace.'\\Temporal',
            default => $rootNamespace
        };

        if ($this->option('scoped')) {
            $namespace = Str::of($this->getNameInput())
                ->when($this->option('interface'), fn ($name) => $name->replaceLast('Interface', ''))
                ->whenEndsWith('Workflow', fn ($name) => $name->replaceLast('Workflow', ''));

            return sprintf('%s\\Workflows\\%s', $rootNamespace, $namespace);
        }

        return sprintf('%s\\Workflows', $rootNamespace);
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
