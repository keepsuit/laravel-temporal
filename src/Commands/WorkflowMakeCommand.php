<?php

namespace Keepsuit\LaravelTemporal\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand('temporal:make:workflow')]
class WorkflowMakeCommand extends GeneratorCommand
{
    use Concerns\Stubs;

    protected $name = 'temporal:make:workflow';

    protected $description = 'Create a new temporal workflow class';

    protected $type = 'Workflow';

    protected function getStub(): string
    {
        return match (true) {
            $this->option('interface') => $this->resolveStubPath('workflow_interface.stub'),
            default => $this->resolveStubPath('workflow.stub'),
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

    protected function getNameInput(): string
    {
        return Str::of(parent::getNameInput())
            ->whenEndsWith('Interface', fn ($name) => $name->replaceLast('Interface', ''))
            ->when($this->option('interface'), fn ($name) => $name->append('Interface'));
    }

    protected function getOptions(): array
    {
        return [
            ['interface', 'i', InputOption::VALUE_NONE, 'Create an interface for the workflow instead of a class'],
            ['scoped', 's', InputOption::VALUE_NONE, 'Create the workflow inside a scoped directory'],
            ['force', 'f', InputOption::VALUE_NONE, 'Create the Interceptor class even if the file already exists'],
        ];
    }
}
