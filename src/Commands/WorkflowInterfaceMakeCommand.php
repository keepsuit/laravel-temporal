<?php

namespace Keepsuit\LaravelTemporal\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class WorkflowInterfaceMakeCommand extends GeneratorCommand
{
    protected $name = 'temporal:make:workflow-interface';

    protected $description = 'Create a temporal workflow interface';

    protected $type = 'Workflow';

    protected $hidden = true;

    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/workflow_interface.stub');
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Workflows';
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
