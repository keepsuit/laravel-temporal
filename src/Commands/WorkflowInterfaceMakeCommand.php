<?php

namespace Keepsuit\LaravelTemporal\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class WorkflowInterfaceMakeCommand extends GeneratorCommand
{
    protected $signature = 'temporal:make:workflow-interface {name}
                        {--scoped : Create the workflow inside a scoped directory}';

    protected $description = 'Create a temporal workflow interface';

    protected $type = 'Workflow';

    protected $hidden = true;

    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/workflow_interface.stub');
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        if ($this->option('scoped')) {
            $workflowName = Str::replaceLast('Interface', '', $this->getNameInput());
            $namespace = Str::endsWith('Workflow', $workflowName)
                ? Str::replaceLast('Workflow', '', $workflowName)
                : $workflowName;

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
        $nameInput = parent::getNameInput();

        return Str::endsWith($nameInput, 'Interface')
            ? $nameInput
            : sprintf('%sInterface', $nameInput);
    }
}
