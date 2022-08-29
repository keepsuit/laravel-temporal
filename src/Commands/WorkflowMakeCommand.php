<?php

namespace Keepsuit\LaravelTemporal\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class WorkflowMakeCommand extends GeneratorCommand
{
    protected $name = 'temporal:make:workflow';

    protected $description = 'Create a temporal workflow class';

    protected $type = 'Workflow';

    public function handle(): ?bool
    {
        $this->callSilently(WorkflowInterfaceMakeCommand::class, [
            'name' => $this->getNameInput(),
        ]);

        return parent::handle();
    }

    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/workflow.stub');
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
            ? Str::replaceLast($nameInput, 'Interface', '')
            : $nameInput;
    }
}
