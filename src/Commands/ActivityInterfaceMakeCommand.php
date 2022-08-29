<?php

namespace Keepsuit\LaravelTemporal\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ActivityInterfaceMakeCommand extends GeneratorCommand
{
    protected $name = 'temporal:make:activity-interface';

    protected $description = 'Create a temporal activity interface';

    protected $type = 'Workflow';

    protected $hidden = true;

    protected function getStub(): string
    {
        return $this->option('local')
            ? $this->resolveStubPath('/stubs/local_activity_interface.stub')
            : $this->resolveStubPath('/stubs/activity_interface.stub');
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Activities';
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

    protected function getOptions(): array
    {
        return [
            ['local', null, InputOption::VALUE_NONE, 'Indicates that activity should be local'],
        ];
    }
}
